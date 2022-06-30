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
        $allow = function (int $requesterID, array $params) {
            return true;
        };

        $allow_for_self = function (int $requesterID, array $params) {
            return $params[0] === $requesterID;
        };
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
                "GET" => [
                    "getSelf" => $allow_for_self,
                    "getAllUsers" => $allow,
                ],
                "PUT" => [
                    "changeOwnContactData" => $allow_for_self,
                    "changeAllUsersContactData" => $allow,
                ],
                "DELETE" => [
                    "deleteSelf" => $allow_for_self,
                    "deleteAllUsers" => $allow,
                ]
            ],
            "/users/{x}/role" => [
                "PUT" => [
                    "changeAllUsersRoles" => $allow,
                ]
            ],
            "/users/{x}/password" => [
                "PUT" => [
                    "changeOwnPassword" => $allow_for_self,
                ]
            ],
            "/users/{x}/password-privileged-change" => [
                "PUT" => [
                    "changeAllUsersPasswordsPrivileged" => $allow,
                ]
            ],
            "/users/{x}/email-change" => [
                "POST" => [
                    "changeOwnEmail" => $allow_for_self,
                ]
            ],
            "/users/{x}/email-change-privileged" => [
                "POST" => [
                    "changeAllUsersEmailPrivileged" => $allow,
                ]
            ],
            "/users/{x}/email-change-verify" => [
                "POST" => [] //no authentication
            ],
            "/users/{x}/logout" => [
                "POST" => [
                    "logoutSelf" => $allow_for_self,
                ]
            ],
            "/users" => [
                "GET" => [
                    "getAllUsers" => $allow,
                ]
            ],
            "/users/length" => [
                "GET" => [
                    "getAllUsers" => $allow,
                ]
            ],
            "/roles" => [
                "GET" => [] //no authentication
            ],
        ];
    }
}
