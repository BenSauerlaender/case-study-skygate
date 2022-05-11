<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\Interfaces\ApiRequestInterface;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\AccessTokenExpiredResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\AccessTokenNotValidResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\AuthenticationRequiredResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\Interfaces\ApiResponseInterface;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\InternalErrorResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\MethodNotAllowedResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\MissingPermissionsResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\ResourceNotFoundResponse;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\ApiControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\AuthenticationControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\RoutingControllerInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\DBException;
use BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions\ApiMethodNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions\ApiPathNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\ExpiredTokenException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\InvalidTokenException;
use Closure;
use Exception;
use InvalidArgumentException;

class ApiController implements ApiControllerInterface
{

    private RoutingControllerInterface $routing;
    private AuthenticationControllerInterface $auth;

    //Attention: This is used inside the route functions
    private array $controller;

    /**
     * Construct the ApiController
     *
     * @param  RoutingControllerInterface        $routing               A RoutingController to choose a route.
     * @param  AuthenticationControllerInterface $auth                  A authenticationController authenticate the request.
     * @param  array<string,mixed>               $additionalController  An Array of additional Controllers (with there names as keys), that can be used from the routes functions.
     */
    public function __construct(RoutingControllerInterface $routing, AuthenticationControllerInterface $auth,  array $additionalController)
    {
        $this->routing = $routing;
        $this->auth = $auth;
        $this->controller = $additionalController;
    }

    public function handleRequest(ApiRequestInterface $request): ApiResponseInterface
    {
        //search for the right route
        try {
            $route = $this->routing->route($request->getPath(), $request->getMethod());
        } catch (ApiPathNotFoundException $e) {
            return new ResourceNotFoundResponse();
        } catch (ApiMethodNotFoundException $e) {
            return new MethodNotAllowedResponse($e->getAvailableMethods());
        }

        //if the route require authentication
        if ($route["requireAuth"]) {

            $accessToken = $request->getAccessToken();
            if (is_null($accessToken)) {
                return new AuthenticationRequiredResponse();
            }

            //authenticate the requester
            try {
                $auth = $this->auth->authenticateAccessToken($accessToken);
            } catch (ExpiredTokenException $e) {
                return new AccessTokenExpiredResponse();
            } catch (InvalidTokenException | InvalidArgumentException $e) {
                return new AccessTokenNotValidResponse();
            }

            //check if the requester has all required permissions for this route
            if (!$this->auth->hasPermission($route, $auth)) {
                return new MissingPermissionsResponse($route["permissions"]);
            }
        }

        /** @var Closure */
        $func = $route["function"];

        //get the ids given in the requested route
        $ids = $route["ids"];

        try {
            //call the routes function in this object
            return $func->call($this, $request, $ids);
        } catch (DBException $e) {
            return new InternalErrorResponse($e);
        } catch (Exception $e) {
            return new InternalErrorResponse($e);
        }
    }
}
