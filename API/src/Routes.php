<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\Interfaces\ApiRequestInterface;

class Routes
{
    public static function getRoutes(): array
    {
        return [
            "/users/{id}" => [
                "GET" => [
                    "ids" => ["userID"],
                    "requireAuth" => true,
                    "permissions" => ["user:read:these"],
                    "function" => function (ApiRequestInterface $req, array $ids) {
                        return null;
                    }
                ]
            ]
        ];
    }
}
