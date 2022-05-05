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
     * @throws  AccessTokenExpiredException if the access token is expired.
     */
    public function authenticateAccessToken(string $accessToken): array;

    /**
     * Generate and Return a new refresh token for the user. Only the new token is valid then.
     * 
     * @param int $userID The User's ID
     */
    public function getNewRefreshToken(int $userID): string;

    /**
     * Generate a new access token.
     *
     * @param  string $refreshToken The requesters refreshToken
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
