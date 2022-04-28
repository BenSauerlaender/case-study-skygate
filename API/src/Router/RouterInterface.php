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
     * @param  string                  $path    The requested path
     * @param  string                  $method  The requested method
     */
    public function route(string $path, string $method): RequestHandlerInterface;
}
