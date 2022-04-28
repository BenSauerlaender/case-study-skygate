<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router;

/**
 * Class to represent an API response
 */
interface ResponseInterface
{
    /**
     * Sends the response to the client.
     */
    public function send(): void;
}
