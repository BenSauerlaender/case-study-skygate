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
