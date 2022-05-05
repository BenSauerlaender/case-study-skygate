<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\Interfaces\ApiResponseInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\ApiControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\AuthenticationControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\RoutingControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\Router\Interfaces\ApiRequestInterface;
use Closure;

class ApiController implements ApiControllerInterface
{

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
        try{
            $route = $this->routing->route($request->getPath, $request->getMethod);
        }catch(){

        }

        //if the route require authentication
        if ($route["requireAuth"]) {

            //authenticate the requester
            $auth = $this->auth->authenticateRequest($request);

            //check if the requester has all required permissions for this route
            if(!$this->routing->hasPermission($route, $auth["permissions"])){
                
            }
        }

        /** @var Closure */
        $func = $route["function"];

        //get the ids given in the requested route
        $ids = $route["ids"];

        //call the routes function in this object
        return $func->call($this, $request, $ids);
    }
}
