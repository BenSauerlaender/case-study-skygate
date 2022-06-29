<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace Controller;

use Objects\Interfaces\RequestInterface;
use Objects\Responses\Interfaces\ResponseInterface;
use Objects\Responses\ServerErrorResponses\InternalErrorResponse;
use Objects\Responses\ClientErrorResponses\MethodNotAllowedResponse;
use Objects\Responses\ClientErrorResponses\MissingPermissionsResponse;
use Objects\Responses\ClientErrorResponses\ResourceNotFoundResponse;
use Controller\Interfaces\ApiControllerInterface;
use Controller\Interfaces\AuthenticationControllerInterface;
use Controller\Interfaces\RoutingControllerInterface;
use DbAccessors\MySqlEcrAccessor;
use DbAccessors\MySqlRefreshTokenAccessor;
use DbAccessors\MySqlRoleAccessor;
use DbAccessors\MySqlUserAccessor;
use DbAccessors\MySqlUserQuery;
use Exceptions\DBExceptions\DBException;
use Exceptions\InvalidRequestExceptions\InvalidPathException;
use Exceptions\InvalidRequestExceptions\NotSecureException;
use Exceptions\RoutingExceptions\ApiMethodNotFoundException;
use Exceptions\RoutingExceptions\ApiPathNotFoundException;
use Exceptions\TokenExceptions\ExpiredTokenException;
use Exceptions\TokenExceptions\InvalidTokenException;
use Objects\Request;
use Objects\Responses\ClientErrorResponses\AuthorizationErrorResponse;
use Closure;
use Controller\Interfaces\PermissionControllerInterface;
use Exception;
use Exceptions\InvalidRequestExceptions\InvalidMethodException;
use InvalidArgumentException;
use Objects\ApiMethod;
use Objects\Responses\SuccessfulResponses\CORSResponse;
use PDO;
use Permissions;

/**
 * Implementation of ApiControllerInterface
 */
class ApiController implements ApiControllerInterface
{
    private RoutingControllerInterface $routingController;
    private AuthenticationControllerInterface $authenticationController;
    private PermissionControllerInterface $permissionController;

    //Attention: This is used inside the route functions
    private array $controller;
    private array $accessors;

    /**
     * Construct the ApiController
     *
     * @param  RoutingControllerInterface        $routingController         A Controller to choose a route.
     * @param  AuthenticationControllerInterface $authenticationController  A Controller to authenticate the request.
     * @param  PermissionControllerInterface     $permissionController      A Controller to check permission of the request.
     * @param  array<string,mixed>               $additionalController      An Array of additional Controllers (with there names as keys), that can be used from the routes functions.
     * @param  array<string,mixed>               $additionalAccessors       An Array of additional Accessors (with there names as keys), that can be used from the routes functions.
     */
    public function __construct(RoutingControllerInterface $routingController, AuthenticationControllerInterface $authenticationController,  array $additionalController, array $additionalAccessors, PermissionControllerInterface $permissionController)
    {
        $this->routingController = $routingController;
        $this->authenticationController = $authenticationController;
        $this->permissionController = $permissionController;
        $this->controller = $additionalController;
        $this->accessors = $additionalAccessors;
    }

    public function fetchRequest(array $server, array $headers, string $pathPrefix, string $bodyJSON = ""): RequestInterface
    {
        //check if all necessary $_SERVER variables are set
        if (!isset($server["REQUEST_URI"]) or !isset($server["REQUEST_METHOD"]) or !isset($server["QUERY_STRING"])) {
            throw new InvalidArgumentException("The server array has not all necessary properties.");
        }

        foreach ($headers as $key => $value) {
            if (!is_string($key) or !is_string($value)) throw new InvalidArgumentException("The headers array contains not only strings.");
        }

        //if in production: check if the connection is secure
        $env = $_ENV["ENVIRONMENT"] ?? "PRODUCTION";
        if ($env === "PRODUCTION" && (!isset($server["HTTPS"]) or empty($server['HTTPS']))) {
            throw new NotSecureException();
        }

        //get path without query
        $path = explode("?", $server["REQUEST_URI"])[0];

        //check if the requested path starts with the api path prefix
        if (!str_starts_with($path, $pathPrefix)) {
            throw new InvalidPathException("The Path: '$path' need to start with: '$pathPrefix'");
        }

        //cut the prefix
        $path = substr($path, strlen($pathPrefix));

        $method = $server["REQUEST_METHOD"];

        $query = $server["QUERY_STRING"];

        //get the body of the request
        if ($bodyJSON !== "" and ($method === "POST" or $method === "PUT")) {
            $body = json_decode($bodyJSON, true, 512, JSON_THROW_ON_ERROR);
        } else {
            $body = null;
        }

        //return the request
        return new Request($path, $method, $query, $headers, $body);
    }

    public function handleRequest(RequestInterface $request): ResponseInterface
    {
        //Handle CORS Options requests
        if ($request->getMethod() === ApiMethod::OPTIONS) {
            try {
                $method = ApiMethod::fromString($request->getHeader("Access-Control-Request-Method") ?? "");
                $route = $this->routingController->route($request->getPath(), $method);
                return new CORSResponse($request);
            } catch (InvalidMethodException | ApiPathNotFoundException | ApiMethodNotFoundException $e) {
                return new ResourceNotFoundResponse();
            }
        }

        //search for the correct route
        try {
            $route = $this->routingController->route($request->getPath(), $request->getMethod());
        } catch (ApiPathNotFoundException $e) {
            //no matching route found
            return new ResourceNotFoundResponse();
        } catch (ApiMethodNotFoundException $e) {
            //found route don't support the requested method
            return new MethodNotAllowedResponse($e->getAvailableMethods());
        }

        //if the route require authentication
        if ($route["requireAuth"]) {

            //get the bearer jwt
            $accessToken = $request->getAccessToken();
            if (is_null($accessToken)) {
                return new AuthorizationErrorResponse("The resource with this method require an JWT Access Token as barrier token. Use GET /token to get one", 101);
            }

            //authenticate the requester via the access token
            try {
                $requester = $this->authenticationController->validateAccessToken($accessToken);
            } catch (ExpiredTokenException $e) {
                return new AuthorizationErrorResponse("The JWT Access Token is expired. Use GET /token to get a new one one", 103);
            } catch (InvalidTokenException | InvalidArgumentException $e) {
                return new AuthorizationErrorResponse("The JWT Access Token is not valid. Use GET /token to get a new one one", 102);
            }

            //check if the requester has all required permissions for this route
            if (!$this->permissionController->isAllowed($request->getPath(), $request->getMethod(), $requester["permissions"], $requester["userID"])) {
                return new MissingPermissionsResponse($route["permissions"]);
            }
        }

        //the route's function to process the request
        /** @var Closure */
        $func = $route["function"];

        //get parameter names, that are used in the requested route
        $parameters = $route["params"];

        try {
            //call the routes function in this objects scope, so that the controller and accessors arrays are available for the function
            return $func->call($this, $request, $parameters);
        } catch (DBException $e) {
            return new InternalErrorResponse($e);
        } catch (Exception $e) {
            return new InternalErrorResponse($e);
        }
    }

    public function sendResponse(ResponseInterface $response, string $domain, string $pathPrefix): void
    {
        //clear all headers
        header_remove();

        //set response code
        http_response_code($response->getCode());

        //set all custom headers
        foreach ($response->getHeaders() as $key => $value) {
            header("$key: $value");
        }

        header("Access-Control-Allow-Origin: http://localhost:3001");
        header("Access-Control-Allow-Credentials: true");

        //set all cookies
        foreach ($response->getCookies() as $cookie) {
            $cookieInfo = $cookie->get();
            setcookie(
                $cookieInfo["name"],
                $cookieInfo["value"],
                [
                    'expires' => ($cookieInfo["expiresIn"] <= 0) ? 0 : ($cookieInfo["expiresIn"] + time()),
                    'path' => $pathPrefix . $cookieInfo["path"],
                    'domain' => $domain,
                    'secure' => $cookieInfo["secure"],
                    'httponly' => $cookieInfo["httpOnly"],
                ]
            );
        }

        //set body if provided
        $body = $response->getJsonBody();
        if ($body !== "") {
            echo $body;
        }
    }


    static function get(PDO $pdo, array $routes): ApiControllerInterface
    {
        //Database Accessors
        $userAccessor           = new MySqlUserAccessor($pdo);
        $roleAccessor           = new MySqlRoleAccessor($pdo);
        $ecrAccessor            = new MySqlEcrAccessor($pdo);
        $refreshTokenAccessor   = new MySqlRefreshTokenAccessor($pdo);
        $userQuery              = new MySqlUserQuery($pdo);

        //controller
        $securityController         = new SecurityController();
        $validationController       = new ValidationController();
        $userController             = new UserController($securityController, $validationController, $userAccessor, $roleAccessor, $ecrAccessor, $refreshTokenAccessor);
        $authenticationController   = new AuthenticationController($userAccessor, $refreshTokenAccessor, $roleAccessor);
        $routingController          = new RoutingController($routes);

        return new self(
            $routingController,
            $authenticationController,
            ["user" => $userController, "auth" => $authenticationController], //Controller
            ["user" => $userAccessor, "userQuery" => $userQuery, "refreshToken" => $refreshTokenAccessor, "role" => $roleAccessor] //Accessors
        );
    }
}
