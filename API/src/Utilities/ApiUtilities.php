<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities;

use BenSauer\CaseStudySkygateApi\Objects\Interfaces\RequestInterface;
use BenSauer\CaseStudySkygateApi\Objects\Request;
use BenSauer\CaseStudySkygateApi\Objects\Responses\Interfaces\ResponseInterface;
use BenSauer\CaseStudySkygateApi\Controller\ApiController;
use BenSauer\CaseStudySkygateApi\Controller\AuthenticationController;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\ApiControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\RoutingController;
use BenSauer\CaseStudySkygateApi\Controller\UserController;
use BenSauer\CaseStudySkygateApi\Controller\ValidationController;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\UserQueryInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlAccessor;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlEcrAccessor;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlRefreshTokenAccessor;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlRoleAccessor;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlUserAccessor;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlUserQuery;
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
    static function getRequest(array $server, array $headers, string $pathPrefix, string $bodyJSON = ""): RequestInterface
    {
        $env = $_ENV["ENVIRONMENT"] ?? "PRODUCTION";
        if (!isset($server["REQUEST_URI"]) or !isset($server["REQUEST_METHOD"]) or !isset($server["QUERY_STRING"])) {
            throw new ShouldNeverHappenException("The _SERVER variables should be always set from the apache server.");
        }

        //check if the connection is secure
        if ($env === "PRODUCTION" && (!isset($server["HTTPS"]) or empty($server['HTTPS']))) {
            throw new NotSecureException();
        }

        $path = explode("?", $server["REQUEST_URI"])[0];
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
        $pdo = DbConnector::getConnection();

        //Database Accessors
        $userAccessor           = new MySqlUserAccessor($pdo);
        $roleAccessor           = new MySqlRoleAccessor($pdo);
        $ecrAccessor            = new MySqlEcrAccessor($pdo);
        $refreshTokenAccessor   = new MySqlRefreshTokenAccessor($pdo);
        $userQuery              = new MySqlUserQuery($pdo);

        //utilities
        $securityUtil           = new SecurityController();

        //controller
        $validationController       = new ValidationController();
        $userController             = new UserController($securityUtil, $validationController, $userAccessor, $roleAccessor, $ecrAccessor);
        $authenticationController   = new AuthenticationController($userAccessor, $refreshTokenAccessor, $roleAccessor);
        $routingController          = new RoutingController(Routes::getRoutes());

        return new ApiController(
            $routingController,
            $authenticationController,
            ["user" => $userController, "auth" => $authenticationController],
            ["user" => $userAccessor, "userQuery" => $userQuery, "refreshToken" => $refreshTokenAccessor, "role" => $roleAccessor]
        );
    }
}
