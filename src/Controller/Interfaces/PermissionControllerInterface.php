<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace Controller\Interfaces;

use Objects\ApiMethod;
use Objects\Interfaces\ApiPathInterface;

/**
 * Controller that decides if a requester is allowed to use a route
 */
interface PermissionControllerInterface
{
    /**
     * Searches for a route that matches path and method and returns the route in convenient array
     *
     * @param  ApiPathInterface     $path           Requested Path.
     * @param  ApiMethod            $method         Requested Method.
     * @param  array<string>        $permissions    A list of Permissions the user has
     * @return bool     True if the requester is allowed to use this route, False otherwise
     */
    public function isAllowed(ApiPathInterface $path, ApiMethod $method, array $permissions, int $requesterID): bool;
}
