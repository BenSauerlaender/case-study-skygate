<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router\Interfaces;

use BenSauer\CaseStudySkygateApi\Router\Responses\Interfaces\ResponseInterface;

/**
 * Interface for RequestHandler
 */
interface RequestHandlerInterface
{
    /**
     * Handles the request and returns a response
     *
     * @param  RequestInterface  $request The request to handle
     * @throws BadRequestHandlerException if the handler is broken.
     */
    public function handle(RequestInterface $request): ResponseInterface;
}
