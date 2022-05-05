<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller\Interfaces;

use BenSauer\CaseStudySkygateApi\Router\Interfaces\ApiRequestInterface;

/**
 * Controller that handles authentication stuff.
 */
interface AuthenticationControllerInterface
{
    /**
     * Authenticates a Requester based on the token provided via the request.
     * 
     * @param  ApiRequestInterface $request The request
     * @return array<string,mixed>  $auth = [
     * 
     * ]
     */
    public function authenticateRequest(ApiRequestInterface $request): array;
}
