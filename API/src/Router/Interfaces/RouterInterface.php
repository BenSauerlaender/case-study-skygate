<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router\Requests;

use BenSauer\CaseStudySkygateApi\Router\RequestHandlerInterface;

/**
 * Interface for Router
 */
interface RouterInterface
{
    /**
     * Chooses the right handler for the path and method
     *
     * @param  RequestPath                     $path    The requested path exploded by /
     * @param  RequestMethod                  $method  The requested method
     */
    public function route(RequestPath $path, RequestMethod $method): RequestHandlerInterface;
}
