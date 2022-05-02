<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Utilities;

use BenSauer\CaseStudySkygateApi\Router\Responses\Interfaces\ResponseInterface;

class RouterUtilities
{
    /**
     * Utilitie function to send a response to the user
     *
     * @param  ResponseInterface $response The response to be send
     */
    static public function sendResponse(ResponseInterface $response): void
    {
        http_response_code($response->getCode());
        //set Server and Date Header
        //add base PATH to Cookie path
    }
}
