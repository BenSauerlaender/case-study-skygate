<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller\Interfaces;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\Interfaces\ApiResponseInterface;
use BenSauer\CaseStudySkygateApi\Router\Interfaces\ApiRequestInterface;

/**
 * Main Controller for the whole api
 */
interface ApiControllerInterface
{
    /**
     * Takes an request, process it and returns a response.
     *
     * @param  ApiRequestInterface  $request
     * @return ApiResponseInterface
     */
    public function handleRequest(ApiRequestInterface $request): ApiResponseInterface;
}
