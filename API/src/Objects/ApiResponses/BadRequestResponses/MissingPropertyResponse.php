<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\ApiResponses\BadRequestResponses;

/**
 * Response that should be used if the route require properties in the request body that are missing.
 */
class MissingPropertyResponse extends BadRequestResponse
{
    public function __construct(array $missing)
    {
        parent::__construct("There are required request-body-properties that are missing.", 101, ["missingProperties" => $missing]);
    }
}
