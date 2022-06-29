<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace Controller;

use Controller\Interfaces\AuthenticationControllerInterface;
use DbAccessors\Interfaces\RefreshTokenAccessorInterface;
use DbAccessors\Interfaces\RoleAccessorInterface;
use DbAccessors\Interfaces\UserAccessorInterface;
use Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use Exceptions\TokenExceptions\ExpiredTokenException;
use Exceptions\TokenExceptions\InvalidTokenException;
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
            $permissionArray = explode(" ", $payload["perm"]);
        }

        //return the requester-object
        return [
            "userID" => $payload["id"],
            "permissions" => $permissionArray,
        ];
    }
}
