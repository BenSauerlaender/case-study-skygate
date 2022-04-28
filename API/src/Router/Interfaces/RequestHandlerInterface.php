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
interface RequestHandlerInterface
{
    public function handle(RequestInterface $request): ResponseInterface;
}
