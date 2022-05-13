<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiMethod;
use BenSauer\CaseStudySkygateApi\ApiComponents\Interfaces\ApiPathInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\RoutingControllerInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions\ApiMethodNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions\ApiPathNotFoundException;



//TODO: RENAME THE IDS THING TO PLACEHOLDERS
class RoutingController implements RoutingControllerInterface
{

    /**
     * An array of all available routes
     *
     * @var array $routes = [
     *   $path (string) => [
     *     $method (string) => [ 
     *       "ids"            => (array<string,mixed>)    Key-Value pair for each path replacement.
     *       "requireAuth"    => (bool)                   True if authentication is required.
     *       "permissions"    => (array<string>)          List of required permissions.
     *       "function"       => (Closure)                Anonymous function that process the route.
     *     ]
     *   ]
     * ]
     */
    private array $routes;

    /**
     * @param  array $routes = [
     *   $path (string) => [
     *     $method (string) => [ 
     *       "ids"            => (array<string,mixed>)    Key-Value pair for each path replacement.
     *       "requireAuth"    => (bool)                   True if authentication is required.
     *       "permissions"    => (array<string>)          List of required permissions.
     *       "function"       => (Closure)                Anonymous function that process the route.
     *     ]
     *   ]
     * ]
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function route(ApiPathInterface $reqPath, ApiMethod $reqMethod): array
    {
        //get the requested path in the same format it is saved in the routes
        $pathWPlaceholder = $reqPath->getStringWithPlaceholders();

        //throw exception if no route-path matches the requested-path
        if (!array_key_exists($pathWPlaceholder, $this->routes)) throw new ApiPathNotFoundException("No route matching the path: $reqPath can be found");

        //get the path object (a list of available methods)
        $path = $this->routes[$pathWPlaceholder];

        //get the requested method as string
        $methodString = $reqMethod->toString();

        //throw exception if there is no route with the requested path available
        if (!array_key_exists($methodString, $path)) {
            throw new ApiMethodNotFoundException("The Path: $reqPath has no method: $methodString;", array_keys($path));
        }

        //get the route
        $route = $path[$methodString];

        //get the requested ids
        $reqIds = $reqPath->getIDs();

        //save the ids with there names as keys
        $idsWithNames = [];
        foreach ($route["ids"] as $i => $key) {
            $idsWithNames[$key] = $reqIds[$i];
        }

        return [
            "ids" => $idsWithNames,
            "requireAuth"   => $route["requireAuth"],
            "permissions"   => $route["permissions"],
            "function"      => $route["function"]
        ];
    }
    public function hasPermission(array $route, array $givenPermissions): bool
    {
        return true;
    }
}
