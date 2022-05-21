<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller\Interfaces;

use BenSauer\CaseStudySkygateApi\Objects\Interfaces\RequestInterface;
use BenSauer\CaseStudySkygateApi\Objects\Responses\Interfaces\ResponseInterface;

/**
 * Main Controller for the whole api
 */
interface ApiControllerInterface
{
    /**
     * Takes an request, process it and returns a response.
     *
     * @param  RequestInterface  $request
     * @return ResponseInterface

     */
    public function handleRequest(RequestInterface $request): ResponseInterface;
}
