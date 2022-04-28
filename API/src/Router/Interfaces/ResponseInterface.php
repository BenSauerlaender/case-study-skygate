<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router\Requests;

/**
 * Interface for Response
 */
interface ResponseInterface
{
    /**
     * Sends the response to the client.
     */
    public function send(): void;
}
