<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace Controller\Interfaces;

/**
 * Controller that handles authentication and authorization  stuff.
 */
interface AuthenticationControllerInterface
{
    /**
     * Generates a new refresh token for the specified user. Only the new token is valid then.
     * 
     * @param string $email The User's email
     * 
     * @return string The new refresh token.
     * 
     * @throws UserNotFoundException if the user do not exist.
     */
    public function getNewRefreshToken(string $email): string;

    /**
     * Validates the refreshToken and generate a new access token.
     *
     * @param  string $refreshToken The requesters refreshToken.
     * 
     * @return string The new access token.
     * 
     * @throws InvalidArgumentException     if the string is not a jwt.
     * @throws InvalidTokenException        if the token is invalid.
     *      (ExpiredTokenException | ...)   if the token is expired.
     * @throws UserNotFoundException        if the user of the token not exists (anymore).
     */
    public function getNewAccessToken(string $refreshToken): string;

    /**
     * Validates a accessToken and returns an requester-array, containing information about the requester and his permissions.
     * 
     * @param  string $accessToken              The Requesters access token.
     * @return array<string,mixed>  $requester = [   
     *      "userID"        => (int)            The Users ID.
     *      "permissions"   => (array<string>)  The Users permissions.
     * ]
     * 
     * @throws InvalidArgumentException     if the string is not a jwt.
     * @throws InvalidTokenException        if the token is invalid.
     *      (ExpiredTokenException | ...)   if the token is expired.
     */
    public function validateAccessToken(string $accessToken): array;

    /**
     * Check if the given requester has all permissions required by the given route
     *
     * @param  array<string> $requester   The requesters permissions.
     * @param  array<string> $route       The routes required permissions.
     * 
     * @throws InvalidArgumentException if one of the permission-arrays are invalid.
     */
    public function hasPermissions(array $requester, array $route): bool;
}
