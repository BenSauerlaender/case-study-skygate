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
                "GET" => [
                    "getSelf" => function (int $requesterID, array $params) {
                        return $params[0] === $requesterID;
                    },
                    "getAllUsers" => function (int $requesterID, array $params) {
                        return true;
                    }
                ],
                "PUT" => [
                    "getOwnContactData" => function (int $requesterID, array $params) {
                        return $params[0] === $requesterID;
                    },
                    "getAllUsersContactData" => function (int $requesterID, array $params) {
                        return true;
                    }
                ],
                "DELETE" => [
                    "deleteSelf" => function (int $requesterID, array $params) {
                        return $params[0] === $requesterID;
                    },
                    "deleteAllUsers" => function (int $requesterID, array $params) {
                        return true;
                    }
                ]
            ],
            "/users/{x}/password" => [
                "PUT" => [
                    "changeOwnPassword" => function (int $requesterID, array $params) {
                        return $params[0] === $requesterID;
                    },
                ]
            ],
            "/users/{x}/email-change" => [
                "POST" => [
                    "changeOwnPassword" => function (int $requesterID, array $params) {
                        return $params[0] === $requesterID;
                    },
                ]
            ],
            "/users/{x}/email-change-verify" => [
                "POST" => [] //no authentication
            ],
            "/users/{x}/logout" => [
                "POST" => [
                    "logoutSelf" => function (int $requesterID, array $params) {
                        return $params[0] === $requesterID;
                    },
                ]
            ],
            "/users" => [
                "GET" => [
                    "getAllUsers" => function (int $requesterID, array $params) {
                        return true;
                    }
                ]
            ],
            "/users/length" => [
                "GET" => [
                    "getAllUsers" => function (int $requesterID, array $params) {
                        return true;
                    }
                ]
            ],
            "/roles" => [
                "GET" => [] //no authentication
            ],
        ];
    }
}
