<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller\Interfaces;

use BenSauer\CaseStudySkygateApi\Objects\Interfaces\RequestInterface;
use BenSauer\CaseStudySkygateApi\Objects\ApiResponses\Interfaces\ApiResponseInterface;

/**
 * Main Controller for the whole api
 */
interface ApiControllerInterface
{
    /**
     * Takes an request, process it and returns a response.
     *
     * @param  RequestInterface  $request
     * @return ApiResponseInterface
     */
    public function handleRequest(RequestInterface $request): ApiResponseInterface;
}
