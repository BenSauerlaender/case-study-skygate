<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router;

/**
 * Class to choose which Route should be used.
 */
interface RouterInterface
{
    /**
     * Chooses the right handler for the path and method
     *
     * @param  ApiPath                     $path    The requested path exploded by /
     * @param  HttpMethod                  $method  The requested method
     */
    public function route(ApiPath $path, HttpMethod $method): RequestHandlerInterface;
}
