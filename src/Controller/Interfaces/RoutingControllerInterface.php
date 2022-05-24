<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller\Interfaces;

use BenSauer\CaseStudySkygateApi\Objects\ApiMethod;
use BenSauer\CaseStudySkygateApi\Objects\Interfaces\ApiPathInterface;

/**
 * Controller that handles all the routes and finds the correct one.
 */
interface RoutingControllerInterface
{
    /**
     * Searches for a route that matches path and method and returns the route in convenient array
     *
     * @param  ApiPathInterface     $path       Requested Path.
     * @param  ApiMethod            $method     Requested Method.
     * @return array<string,mixed>  $route      The found route.
     *   $route = [
     *     "params"         => (array<string,mixed>)    Key-Value pair for each route paramters.
     *     "requireAuth"    => (bool)                   True if authentication is required.
     *     "permissions"    => (array<string>)          List of required permissions.
     *     "function"       => (Closure)                Anonymous function that process the route.
     *   ]
     * @throws ApiPathNotFoundException     if there is no route with this path.
     * @throws ApiMethodNotFoundException   if there is no route with this path, that uses this method.
     */
    public function route(ApiPathInterface $path, ApiMethod $method): array;
}
