<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller;

use BenSauer\CaseStudySkygateApi\Controller\Interfaces\AuthenticationControllerInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RefreshTokenAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\ExpiredTokenException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\InvalidTokenException;
use InvalidArgumentException;
use ReallySimpleJWT\Token;

/**
 * Implementation of AuthenticationControllerInterface
 */
class AuthenticationController implements AuthenticationControllerInterface
{
    private UserAccessorInterface $userAccessor;
    private RefreshTokenAccessorInterface $refreshTokenAccessor;
    private RoleAccessorInterface $roleAccessor;

    public function __construct(UserAccessorInterface $userAccessor, RefreshTokenAccessorInterface $refreshTokenAccessor, RoleAccessorInterface $roleAccessor)
    {
        $this->userAccessor = $userAccessor;
        $this->refreshTokenAccessor = $refreshTokenAccessor;
        $this->roleAccessor = $roleAccessor;
    }

    public function getNewRefreshToken(string $email): string
    {
        //get the userID from the database
        $userID = $this->userAccessor->findByEmail($email);
        if (is_null($userID)) throw new UserNotFoundException(null, $email);

        //increase the refreshTokenCount by 1, so no other refreshToken is valid anymore
        $this->refreshTokenAccessor->increaseCount($userID);

        //get the new count
        $count = $this->refreshTokenAccessor->getCountByUserID($userID);

        $payload = [
            'exp' => time() + 60 * 60 * 24 * 30, //valid for 30days
            'id'  => $userID,
            'cnt' => $count
        ];

        //create and return the JWT 
        $token = Token::customPayload($payload, $_ENV["REFRESH_TOKEN_SECRET"]);
        return $token;
    }

    public function getNewAccessToken(string $refreshToken): string
    {
        //check if the string is a JWT
        if (!Token::validate($refreshToken, $_ENV["REFRESH_TOKEN_SECRET"])) {
            throw new InvalidArgumentException();
        }

        //check if the JWT is not expired
        if (!Token::validateExpiration($refreshToken)) {
            throw new ExpiredTokenException();
        }

        $payload = Token::getPayLoad($refreshToken);
        $userID = $payload["id"];

        //get the users current refresh token count
        $rtCount = $this->refreshTokenAccessor->getCountByUserID($userID);
        if (is_null($rtCount)) throw new UserNotFoundException($userID);

        //check if the token is valid
        if ($rtCount !== $payload["cnt"]) {
            throw new InvalidTokenException("The Token is no longer valid");
        }

        //get the users permissions from the database
        $roleID = $this->userAccessor->get($userID)["roleID"];
        $permissions = $this->roleAccessor->get($roleID)["permissions"];

        //replace the "{userID}" placeholder by the users id
        $permissions = str_replace("{userID}", "$userID", $permissions);

        $payload = [
            'exp' => time() + 60 * 15, //valid for 15 minutes
            'id'  => $userID,
            'perm' => $permissions
        ];

        //create and return the JWT 
        $token = Token::customPayload($payload, $_ENV["ACCESS_TOKEN_SECRET"]);
        return $token;
    }

    public function validateAccessToken(string $accessToken): array
    {
        //check if the string is a valid JWT
        if (!Token::validate($accessToken, $_ENV["ACCESS_TOKEN_SECRET"])) {
            throw new InvalidArgumentException();
        }

        //check if the JWT is not expired
        if (!Token::validateExpiration($accessToken)) {
            throw new ExpiredTokenException();
        }

        //get the payload 
        $payload = Token::getPayLoad($accessToken);

        if ($payload["perm"] === "") {
            $permissionArray = [];
        } else {
            //permissions string to array
            $permissionArray = explode(";", $payload["perm"]);
        }

        //return the requester-object
        return [
            "userID" => $payload["id"],
            "permissions" => $permissionArray,
        ];
    }

    /**
     * A list of resources, for those permissions can be defined
     */
    private const RECOURSES = ["user"];

    /**
     * A list of methods, for those permissions can be defined
     */
    private const METHODS = ["create", "read", "update", "delete"];

    public function hasPermissions(array $givenPermissions, array $requiredPermissions): bool
    {
        //parse the permission arrays into convenient nested objects
        $givenPermissions = $this->parsePermissions($givenPermissions);
        $requiredPermissions = $this->parsePermissions($requiredPermissions);

        //for each required resource
        foreach ($requiredPermissions as $resource => $methods) {

            //check if the user has any permissions for that resource
            if (!isset($givenPermissions[$resource])) return false;

            //for each required method
            foreach ($methods as $method => $scope) {

                //check if user have any permissions for that method
                if (!isset($givenPermissions[$resource][$method])) return false;

                //if the user has not permissions for the whole scope
                if ($givenPermissions[$resource][$method] !== "{all}") {

                    //if permission for the whole scope is required
                    if ($scope === "{all}") return false;

                    //if not: check if the user has the rights for the required scope
                    if ($scope !== $givenPermissions[$resource][$method]) return false;
                }
            }
        }
        //the user has all permissions
        return true;
    }

    /**
     * Converts a list of permission-strings in an nested permission-object
     *
     * @param  array<string> $permissions
     * @return array<string,array<string,string|int>  $nestedPermissions
     *  $nestedPermissions = [
     *    (string) resource => [
     *       (string) method => (string|int) The scope.
     *    ]
     *  ]
     * @throws InvalidArgumentException if one of the permission strings is invalid
     */
    private function parsePermissions(array $permissions): array
    {
        //the object to return
        $ret = [];

        //go through each permission string
        foreach ($permissions as $permString) {

            //explode the permission string in its parts.
            //expect the string to have the following format: "<resource>:<method>:<scope>"
            $permStringExplode = explode(":", $permString);
            if (sizeof($permStringExplode) !== 3) throw new InvalidArgumentException("$permString is not a valid permission string");

            //a list of resources that this string applies permissions to 
            $resources = [];

            //if all-permission: add all resources, otherwise only the specified (when it is available)
            $res = $permStringExplode[0];
            if ($res === "{all}") {
                $resources = self::RECOURSES;
            } else {
                if (!in_array($res, self::RECOURSES)) throw new InvalidArgumentException("$res is not a valid resource");
                array_push($resources, $res);
            }

            //for each of the resources
            foreach ($resources as $resource) {
                //add the resource as key to the return array (if not already there)
                if (!isset($ret[$resource])) $ret[$resource] = [];

                //a list of methods that this string applies permissions to 
                $methods = [];

                //if all -> add all methods, otherwise only the specified (when it is available)
                $method = $permStringExplode[1];
                if ($method === "{all}") {
                    $methods = self::METHODS;
                } else {
                    if (!in_array($method, self::METHODS)) throw new InvalidArgumentException("$method is not a valid method");
                    array_push($methods, $method);
                }

                //for each method
                foreach ($methods as $meth) {

                    //get the scope (either its {all} or a specified id (int))
                    $scope = $permStringExplode[2];

                    //save an int as int. and throw error if its neither an id nor {all}
                    if (ctype_digit($scope)) {
                        $scope = (int) $scope;
                    } else if ($scope !== "{all}") {
                        throw new InvalidArgumentException("$scope is not a valid scope");
                    }

                    //add scope and method to the return array
                    $ret[$resource][$meth] = $scope;
                }
            }
        }
        return $ret;
    }
}
