<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\BadRequestResponses;

/**
 * Response that should be used if the request provide body-properties that are not supported.
 */
class InvalidQueryResponse extends BadRequestResponse
{
    public function __construct(array $invalidQuery)
    {
        parent::__construct("There are parts of the query string that are invalid.", 111, ["invalidQuery" => $invalidQuery]);
    }
}
