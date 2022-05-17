<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\BadRequestResponses;

/**
 * Response that should be used if the request provide body-properties that are not supported.
 */
class InvalidPropertyResponse extends BadRequestResponse
{
    public function __construct(array $invalidProp)
    {
        parent::__construct("There are request-body-properties that are invalid.", 102, ["invalidProperties" => $invalidProp]);
    }
}
