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

class RoutingController implements RoutingControllerInterface
{

    /**
     * An array of all available routes
     *
     * @var array TODO
     */
    private array $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function route(ApiPathInterface $reqPath, ApiMethod $reqMethod): array
    {

        $pathWPlaceholder = $reqPath->getStringWithPlaceholders();

        if (!array_key_exists($pathWPlaceholder, $this->routes)) throw new ApiPathNotFoundException("No route matching the path: $reqPath can be found");

        $path = $this->routes[$pathWPlaceholder];


        $methodString = $reqMethod->toString();

        if (!array_key_exists($methodString, $path)) {
            $availableMethods = implode(",", array_keys($path));
            throw new ApiMethodNotFoundException("The Path: $reqPath has no method: $methodString; Available Methods are: [$availableMethods];");
        }

        $route = $path[$methodString];

        $reqIds = $reqPath->getIDs();

        $paramWithNames = [];
        foreach ($route["ids"] as $i => $key) {
            $paramWithNames[$key] = $reqIds[$i];
        }

        return [
            "ids" => $paramWithNames,
            "requireAuth"   => $route["requireAuth"],
            "permissions"   => $route["permissions"],
            "function"      => $route["function"]
        ];
    }
}
