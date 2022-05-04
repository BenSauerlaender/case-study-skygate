<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiMethod;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiPath;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\RoutingControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;

class RoutingController implements RoutingControllerInterface
{

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

    public function route(ApiPath $path, ApiMethod $method): array
    {
    }
}
