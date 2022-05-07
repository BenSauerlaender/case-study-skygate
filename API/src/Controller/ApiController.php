<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\AccessTokenExpiredResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\AccessTokenNotValidResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\AuthenticationRequiredResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\AuthorizationRequiredResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\Interfaces\ApiResponseInterface;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\InternalErrorResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\MethodNotAllowedResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\MissingPermissionsResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\ResourceNotFoundResponse;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\ApiControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\AuthenticationControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\RoutingControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\DBException;
use BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions\ApiMethodNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\RoutingExceptions\ApiPathNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\ExpiredTokenException;
use BenSauer\CaseStudySkygateApi\Exceptions\TokenExceptions\InvalidTokenException;
use BenSauer\CaseStudySkygateApi\Router\Interfaces\ApiRequestInterface;
use Closure;
use Exception;
use InvalidArgumentException;

class ApiController implements ApiControllerInterface
{

    //Attention: although a controller is not used in this class, they can be used by the routes!
    private RoutingControllerInterface $routing;
    private AuthenticationControllerInterface $auth;
    private UserControllerInterface $uc;

    public function __construct(RoutingControllerInterface $routing, AuthenticationControllerInterface $auth, UserControllerInterface $uc)
    {
        $this->uc = $uc;
        $this->routing = $routing;
        $this->auth = $auth;
    }

    public function handleRequest(ApiRequestInterface $request): ApiResponseInterface
    {
        //search for the right route
        try {
            $route = $this->routing->route($request->getPath, $request->getMethod);
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
            throw new InternalErrorResponse("There are Database problems. Try again later or contact the support.");
        } catch (Exception $e) {
            throw new InternalErrorResponse("There are internal problems. Try again later or contact the support.");
        }
    }
}
