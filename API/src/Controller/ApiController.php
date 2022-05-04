<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\ApiMethod;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\ApiPath;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\Interfaces\ApiResponseInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\ApiControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\Router\Interfaces\ApiRequestInterface;
use Closure;

class ApiController implements ApiControllerInterface
{

    /**
     * The Controller to perform actions related to the user
     */
    private UserControllerInterface $uc;

    /**
     * An array of all available routes
     *
     * @var array TODO
     */
    private array $routes;

    public function __construct(array $routes, UserControllerInterface $uc)
    {
        $this->uc = $uc;
        $this->routes = $routes;
    }

    public function handleRequest(ApiRequestInterface $request): ApiResponseInterface
    {

        //search for the right route
        $route = $this->getRoute($request->getPath, $request->getMethod);

        //if the route require authentication
        if ($route["requireAuthentication"]) {

            //authenticate the requester
            $auth = $this->authenticate($request);

            //check if the requester has all required permissions for this route
            $this->checkPermission($route["requiredPermissions"], $auth["permissions"]);
        }

        //process the request in the route
        return $this->runRoute($route[""], $request);
    }

    /**
     * Searches for matching route and returns it in convenient array
     *
     * @param  ApiPath   $path      The Requested ApiPath 
     * @param  ApiMethod $method    The Requested ApiMethod
     * @return array<>              TODO
     */
    private function getRoute(ApiPath $path, ApiMethod $method): array
    {
    }

    private function authenticate(ApiRequestInterface $req): void
    {
    }
    private function checkPermission(array $requiredPermissions, array $permissions): void
    {
    }
    private function runRoute(Closure $route, ApiRequestInterface $req): ApiResponseInterface
    {
    }
}
