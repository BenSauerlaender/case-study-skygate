<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\Interfaces\ApiRequestInterface;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\Request;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\Interfaces\ApiResponseInterface;
use BenSauer\CaseStudySkygateApi\Controller\ApiController;
use BenSauer\CaseStudySkygateApi\Controller\AuthenticationController;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\ApiControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\RoutingController;
use BenSauer\CaseStudySkygateApi\Controller\UserController;
use BenSauer\CaseStudySkygateApi\Controller\ValidationController;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlEcrAccessor;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlRefreshTokenAccessor;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlRoleAccessor;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlUserAccessor;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiCookieException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiHeaderException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiPathException;
use BenSauer\CaseStudySkygateApi\Exceptions\NotSecureException;
use BenSauer\CaseStudySkygateApi\Exceptions\ShouldNeverHappenException;
use BenSauer\CaseStudySkygateApi\Routes;
use JsonException;

class ApiUtilities
{
    /**
     * Utility function to send a response to the user
     *
     * @param  ApiResponseInterface $response The response to be send
     * @param  string $domain The Servers Domain.
     */
    static public function sendResponse(ApiResponseInterface $response, string $domain, string $basePath): void
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
                $basePath . $cookieInfo["path"],
                $domain,
                $cookieInfo["secure"],
                $cookieInfo["httpOnly"]
            );
        }

        //set data if provided
        $data = $response->getJsonString();
        if ($data !== "") {
            echo $data;
        }
    }

    /**
     * Constructs a request with all needed variables
     *
     * @param  array               $server      The $_SERVER array.
     * @param  array               $headers     The response array of getallheaders().
     * @param  string              $pathPrefix  The prefix in front of an api path e.g. /api/v1/.
     *
     * @throws NotSecureException           if the request comes not from https in prod.
     * @throws InvalidApiPathException      if the path string can not parsed into an ApiPath.
     * @throws InvalidApiMethodException    if the method string can not parsed into an ApiMethod.
     * @throws InvalidApiQueryException     if the query string can not be parsed into an valid array.
     * @throws InvalidApiHeaderException    if a header can not be parsed into an valid array.
     */
    static function getRequest(array $server, array $headers, string $pathPrefix, string $bodyJSON = ""): ApiRequestInterface
    {
        $env = $_ENV["ENVIRONMENT"] ?? "PRODUCTION";
        if (!isset($server["REQUEST_URI"]) or !isset($server["REQUEST_METHOD"]) or !isset($server["QUERY_STRING"])) {
            throw new ShouldNeverHappenException("The _SERVER variables should be always set from the apache server.");
        }

        //check if the connection is secure
        if ($env === "PRODUCTION" && (!isset($server["HTTPS"]) or empty($server['HTTPS']))) {
            throw new NotSecureException();
        }

        $path = $server["REQUEST_URI"];
        //check if the requested path starts with the api path prefix
        if (!str_starts_with($path, $pathPrefix)) {
            throw new InvalidApiPathException("The Path: '$path' need to start with: '$pathPrefix'");
        }

        //cut the prefix
        $path = substr($path, strlen($pathPrefix));

        $method = $server["REQUEST_METHOD"];

        $query = $server["QUERY_STRING"];
        //get the body of the request

        if ($bodyJSON !== "" and ($method === "POST" or $method === "PUT")) {
            $body = json_decode($bodyJSON, true);
            if ($body === NULL) {
                throw new JsonException("The decoding of the body string failed");
            }
        } else {
            $body = null;
        }

        try {
            return new Request($path, $method, $query, $headers, $body);
        } catch (InvalidApiCookieException $e) {
            throw new InvalidApiHeaderException("The Cookie header is invalid.", 0, $e);
        }
    }
    static function getApiController(): ApiControllerInterface
    {
        //Database connection
        $pdo = MySqlConnector::getConnection();

        //Database Accessors
        $userAccessor           = new MySqlUserAccessor($pdo);
        $roleAccessor           = new MySqlRoleAccessor($pdo);
        $ecrAccessor            = new MySqlEcrAccessor($pdo);
        $refreshTokenAccessor   = new MySqlRefreshTokenAccessor($pdo);

        //utilities
        $securityUtil           = new SecurityUtilities();
        $validationController   = new ValidationController();

        //controller
        $userController             = new UserController($securityUtil, $validationController, $userAccessor, $roleAccessor, $ecrAccessor);
        $authenticationController   = new AuthenticationController($userAccessor, $refreshTokenAccessor, $roleAccessor);
        $routingController          = new RoutingController(Routes::getRoutes());

        return new ApiController($routingController, $authenticationController, ["user" => $userController], ["user" => $userAccessor]);
    }
}
