<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router;

/**
 * Class to represent an API request
 */
interface RequestInterface
{
    /**
     * Handles the request and returns a response
     *
     * @param  RequestInterface  $request The request to handle
     */
    public function handle(RequestInterface $request): ResponseInterface;
}
