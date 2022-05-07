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
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\ExpiredTokenException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\InvalidTokenException;
use InvalidArgumentException;
use ReallySimpleJWT\Token;

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

    public function authenticateAccessToken(string $accessToken): array
    {
        //check if the string is a JWT
        if (!Token::validate($accessToken, $_ENV["ACCESS_TOKEN_SECRET"])) {
            throw new InvalidArgumentException();
        }

        //check if the JWT is not expired
        if (!Token::validateExpiration($accessToken)) {
            throw new ExpiredTokenException();
        }

        //get the payload - return it
        $payload = Token::getPayLoad($accessToken);
        return [
            "ids" => ["userID" => $payload["id"]],
            "permissions" => $payload["perm"]
        ];
    }


    public function getNewRefreshToken(int $userID): string
    {
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

        //get the users current refresh token count
        $rtCount = $this->refreshTokenAccessor->getCountByUserID($payload["id"]);

        //check if the token is valid
        if ($rtCount !== $payload["cnt"]) {
            throw new InvalidTokenException("The Token is no longer valid");
        }

        //get the users permissions
        $roleID = $this->userAccessor->get($payload["id"])["roleID"];
        $permissions = $this->roleAccessor->get($roleID)["permissions"];

        $payload = [
            'exp' => time() + 60 * 15, //valid for 15 minutes
            'id'  => $payload["id"],
            'perm' => $permissions
        ];

        //create and return the JWT 
        $token = Token::customPayload($payload, $_ENV["REFRESH_TOKEN_SECRET"]);
        return $token;
    }

    /**
     * A list of resources, for those permissions can be defined
     */
    private const RECOURSES = ["user"];
    /**
     * A list of methods, for those permissions can be defined
     */
    private const METHODS = ["create", "read", "update", "delete"];

    public function hasPermission(array $route, array $auth): bool
    {
        //parse the permission arrays into convenient nested objects
        $reqPerm = $this->parsePermissionArray($route);
        $userPerm = $this->parsePermissionArray($auth);

        //for each required resource
        foreach ($reqPerm as $res => $methods) {

            //check if user have any permissions for that resource
            if (!isset($userPerm[$res])) return false;

            //for each required method
            foreach ($methods as $method => $scope) {

                //check if user have any permissions for that method
                if (!isset($userPerm[$res][$method])) return false;

                //if the user has not permissions for the whole scope
                if ($userPerm[$res][$method] !== "{all}") {

                    //if permission for the whole scope is required
                    if ($scope === "{all}") return false;

                    //if not has the user the rights for the required scope
                    if ($scope !== $userPerm[$res][$method]) return false;
                }
            }
        }
        //the user has all permissions
        return true;
    }


    /**
     * Converts an object with "permissions" and "ids" in an nested object
     *
     * @param  array<string,array<string>|array<int>> $obj
     * @return array<string,array<string,string|int>  $nestedPermissions
     *  $nestedPermissions = [
     *    (string) resource => [
     *       (string) method => (string|int) The scope.
     *    ]
     *  ]
     */
    private function parsePermissionArray(array $obj): array
    {
        //check if all keys are present
        if (!isset($obj["permissions"]) or !isset($obj["ids"])) throw new InvalidArgumentException("One of the Arguments has not all necessary fields");
        if (!is_array($obj["permissions"]) or !is_array($obj["ids"])) throw new InvalidArgumentException("One of the Arguments has fields with invalid types");

        //the object to return
        $permissions = [];

        //go through each permission string
        foreach ($obj["permissions"] as $permString) {

            //explode the string.
            //expect the string to have the following format: "<resource>:<method>:<scope>"
            $permStringExplode = explode(":", $permString);
            if (sizeof($permStringExplode) !== 3) throw new InvalidArgumentException("$permString is not a valid permission string");

            //a list of resources that this string applies permissions to 
            $resources = [];

            //if all -> add all resources, otherwise only the specified (when it is available)
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
                if (!isset($permissions[$resource])) $permissions[$resource] = [];

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

                    //get the scope (either its {all} or a specified id from the ids array or invalid)
                    $scope = $permStringExplode[2];
                    //replace the id-name with the id itself
                    if ($scope !== "{all}") {
                        if (!substr($scope, 1, 1) === "{" or !substr($scope, -1, 1) === "}") throw new InvalidArgumentException("$scope is not a valid scope");

                        $id = substr($scope, 1, -1);
                        if (!isset($obj["ids"][$id]) or !is_int($obj["ids"][$id])) throw new InvalidArgumentException("$id cant be found in one of the id-arrays");
                        $scope = $obj["ids"][$id];
                    }
                    //safe the method together with the scope to the nested array
                    $permissions[$resource][$meth] = $scope;
                }
            }
        }
        return $permissions;
    }
}
