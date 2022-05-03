<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router;

use BenSauer\CaseStudySkygateApi\Router\Requests\RequestMethod;
use BenSauer\CaseStudySkygateApi\Router\Requests\RouterInterface;
use Closure;
use InvalidArgumentException;

/**
 * Class to choose which Route should be used.
 */
class Router implements RouterInterface
{
    /**
     * Sets a function that can build a set of specified Controllers.
     *
     * @param array<string> $availableController    A list of ControllerInterfaces, that are available via the controllerBuilderFunction
     * @param $controllerBuilderFunction
     * 
     * @throws InvalidArgumentException     if the array is empty or if at least one of the Controllers has already a Builder.
     */
    public function addControllerBuilderFunction(array $availableController, Closure $controllerBuilderFunction): void
    {
    }

    /**
     * Adds a route to the Router
     *
     * @param  string  $path                        The Api Path that the new route can handle.
     * @param  string  $method                      The HTTP Method the new route can handle.
     * @param  array<string>   $requiredController  A list of required Controllers.
     * @param  Closure $handlerFunction             The handlerFunction that handles Requests to the Route.
     * 
     * @throws InvalidRequestPathException      if the path is not valid.
     * @throws InvalidRequestMethodException    if the method is not valid.
     * @throws MissingControllerException       if at least one of the required controllers is not present in the Router.
     * @throws DuplicateRouteException          if there is already a route for this path and method.
     */
    public function addRoute(string $path, string $method, bool $requireAuthentication, array $requiredRights, array $requiredController, Closure $handlerFunction): self
    {
        return $this;
    }

    public function route(RequestPath $path, RequestMethod $method): RequestHandlerInterface
    {
    }
}
