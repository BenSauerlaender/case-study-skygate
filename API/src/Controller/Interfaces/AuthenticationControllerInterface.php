<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller\Interfaces;

/**
 * Controller that handles authentication and permission stuff.
 */
interface AuthenticationControllerInterface
{
    /**
     * Authenticates a Requester based on the token provided via the request.
     * 
     * @param  string $accessToken The Requesters access token
     * @return array<string,mixed>  $auth = [
     *      "userID"        => (int)            The Users ID.
     *      "permissions"   => (array<string>)  The Users Permissions.
     * ]
     * 
     * @throws InvalidArgumentException     if the string is not a jwt.
     * @throws InvalidTokenException        if the token is invalid.
     *      (ExpiredTokenException | ...)   if the token is expired.
     */
    public function authenticateAccessToken(string $accessToken): array;

    /**
     * Generate and Return a new refresh token for the user. Only the new token is valid then.
     * 
     * @param int $userID The User's ID
     * 
     * @return string The new refresh token.
     */
    public function getNewRefreshToken(int $userID): string;

    /**
     * Generate a new access token.
     *
     * @param  string $refreshToken The requesters refreshToken
     * 
     * @return string The new access token.
     * 
     * @throws InvalidArgumentException     if the string is not a jwt.
     * @throws InvalidTokenException        if the token is invalid.
     *      (ExpiredTokenException | ...)   if the token is expired.
     */
    public function getNewAccessToken(string $refreshToken): string;

    /**
     * Check if the given Permissions are enough to use the specified route
     *
     * @param  array $route         The route to use.
     * @param  array $permissions   The given Permission.
     */
    public function hasPermission(array $route, array $givenPermissions): bool;
}
