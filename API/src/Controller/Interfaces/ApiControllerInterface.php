<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller\Interfaces;

use BenSauer\CaseStudySkygateApi\Objects\ApiRequests\Interfaces\ApiRequestInterface;
use BenSauer\CaseStudySkygateApi\Objects\ApiResponses\Interfaces\ApiResponseInterface;

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
