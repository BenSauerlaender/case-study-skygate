<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

use Objects\Interfaces\RequestInterface;

/**
 * Class to hold the permissions definitions.
 */
class Permissions
{
    /**
     * Returns a list of all permissions definitions in an convenient array
     * 
     * @return array<string,array<string,array<string,Closure>>>    $permissions = [
     *      <route_path> => [
     *          <route_method> => [
     *              <permission> => (Closure) The function(closure) to determine if the user is allowed to execute the specific route
     *          ]
     *      ]
     * ]
     * 
     */
    public static function getPermissions(): array
    {
        return [
            "/register" => [
                "POST" => [] //no authentication
            ],
            "/users/{x}/verify" => [
                "POST" => [] //no authentication
            ],
            "/login" => [
                "POST" => [] //no authentication
            ],
            "/token" => [
                "GET" => [] //no authentication
            ],
            "/users/{x}" => [
                "GET" => [],
                "PUT" => [],
                "DELETE" => []
            ],
            "/users/{x}/password" => [
                "PUT" => []
            ],
            "/users/{x}/email-change" => [
                "POST" => []
            ],
            "/users/{x}/email-change-verify" => [
                "POST" => []
            ],
            "/users/{x}/logout" => [
                "POST" => []
            ],
            "/users" => [
                "GET" => []
            ],
            "/users/length" => [
                "GET" => []
            ],
            "/roles" => [
                "GET" => [] //no authentication
            ],
        ];
    }
}
