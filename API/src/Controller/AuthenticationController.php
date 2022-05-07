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

    private const RECOURSES = ["user"];
    private const METHODS = ["create", "read", "update", "delete"];

    public function hasPermission(array $route, array $auth): bool
    {
        $reqPerm = $this->resolvePermissionArray($route);
        $userPerm = $this->resolvePermissionArray($auth);

        foreach ($reqPerm as $res => $methods) {
            if (!isset($userPerm[$res])) return false;
            foreach ($methods as $method => $scope) {
                if (!isset($userPerm[$res][$method])) return false;
                if ($userPerm[$res][$method] !== "{all}") {
                    if ($scope === "{all}") return false;
                    if ($scope !== $userPerm[$res][$method]) return false;
                }
            }
        }
        return true;
    }

    private function resolvePermissionArray(array $obj): array
    {
        if (!isset($obj["permissions"]) or !isset($obj["ids"])) throw new InvalidArgumentException("One of the Arguments has not all necessary fields");
        if (!is_array($obj["permissions"]) or !is_array($obj["ids"])) throw new InvalidArgumentException("One of the Arguments has fields with invalid types");

        $permissions = [];

        foreach ($obj["permissions"] as $permString) {
            $permStringExplode = explode(":", $permString);
            if (sizeof($permStringExplode) !== 3) throw new InvalidArgumentException("$permString is not a valid permission string");

            $resources = [];

            $res = $permStringExplode[0];

            if ($res === "{all}") {
                $resources = self::RECOURSES;
            } else {
                if (!in_array($res, self::RECOURSES)) throw new InvalidArgumentException("$res is not a valid resource");
                array_push($resources, $res);
            }

            foreach ($resources as $resource) {
                if (!isset($permissions[$resource])) $permissions[$resource] = [];
                $methods = [];

                $method = $permStringExplode[1];

                if ($method === "{all}") {
                    $methods = self::METHODS;
                } else {
                    if (!in_array($method, self::METHODS)) throw new InvalidArgumentException("$method is not a valid method");
                    array_push($methods, $method);
                }
                foreach ($methods as $meth) {
                    $scope = $permStringExplode[2];
                    if ($scope !== "{all}") {
                        if (!substr($scope, 1, 1) === "{" or !substr($scope, -1, 1) === "}") throw new InvalidArgumentException("$scope is not a valid scope");

                        $id = substr($scope, 1, -1);
                        if (!isset($obj["ids"][$id]) or !is_int($obj["ids"][$id])) throw new InvalidArgumentException("$id cant be found in one of the id-arrays");
                        $scope = $obj["ids"][$id];
                    }
                    $permissions[$resource][$meth] = $scope;
                }
            }
        }
        return $permissions;
    }
}
