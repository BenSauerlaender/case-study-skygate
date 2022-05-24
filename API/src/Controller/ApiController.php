<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller;

use BenSauer\CaseStudySkygateApi\Objects\Interfaces\RequestInterface;
use BenSauer\CaseStudySkygateApi\Objects\Responses\Interfaces\ResponseInterface;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ServerErrorResponses\InternalErrorResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\MethodNotAllowedResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\MissingPermissionsResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\ResourceNotFoundResponse;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\ApiControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\AuthenticationControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\RoutingControllerInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlEcrAccessor;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlRefreshTokenAccessor;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlRoleAccessor;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlUserAccessor;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlUserQuery;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\DBException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\InvalidPathException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\NotSecureException;
use BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions\ApiMethodNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions\ApiPathNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\ExpiredTokenException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\InvalidTokenException;
use BenSauer\CaseStudySkygateApi\Objects\Request;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\AuthorizationErrorResponse;
use Closure;
use Exception;
use InvalidArgumentException;
use JsonException;
use PDO;

/**
 * Implementation of ApiControllerInterface
 */
class ApiController implements ApiControllerInterface
{
    private RoutingControllerInterface $routingController;
    private AuthenticationControllerInterface $authenticationController;

    //Attention: This is used inside the route functions
    private array $controller;
    private array $accessors;

    /**
     * Construct the ApiController
     *
     * @param  RoutingControllerInterface        $routingController         A RoutingController to choose a route.
     * @param  AuthenticationControllerInterface $authenticationController  A authenticationController authenticate the request.
     * @param  array<string,mixed>               $additionalController      An Array of additional Controllers (with there names as keys), that can be used from the routes functions.
     * @param  array<string,mixed>               $additionalAccessors       An Array of additional Accessors (with there names as keys), that can be used from the routes functions.
     */
    public function __construct(RoutingControllerInterface $routingController, AuthenticationControllerInterface $authenticationController,  array $additionalController, array $additionalAccessors)
    {
        $this->routingController = $routingController;
        $this->authenticationController = $authenticationController;
        $this->controller = $additionalController;
        $this->accessors = $additionalAccessors;
    }

    public function fetchRequest(array $server, array $headers, string $pathPrefix, string $bodyJSON = ""): RequestInterface
    {
        //check if all necessary $_SERVER variables are set
        if (!isset($server["REQUEST_URI"]) or !isset($server["REQUEST_METHOD"]) or !isset($server["QUERY_STRING"])) {
            throw new InvalidArgumentException("The server array has not all necessary properties.");
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
            if (!$this->authenticationController->hasPermissions($requester["permissions"], $route["permissions"])) {
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
            return new InternalErrorResponse($parameters);
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

        //set all cookies
        foreach ($response->getCookies() as $cookie) {
            $cookieInfo = $cookie->get();
            setcookie(
                $cookieInfo["name"],
                $cookieInfo["value"],
                ($cookieInfo["expiresIn"] <= 0) ? 0 : ($cookieInfo["expiresIn"] + time()),
                $pathPrefix . $cookieInfo["path"],
                $domain,
                $cookieInfo["secure"],
                $cookieInfo["httpOnly"]
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
        $userController             = new UserController($securityController, $validationController, $userAccessor, $roleAccessor, $ecrAccessor);
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
