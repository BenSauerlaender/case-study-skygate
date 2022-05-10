<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses;

/**
 * Response that should be used if the route require properties in the request body that are missing.
 */
class MissingPropertyResponse extends BaseResponse
{
    public function __construct(array $missing)
    {
        $this->setCode(400);
        $this->setData(["msg" => "There are required request-body-properties that are missing.", "missingProperties" => $missing]);
    }
}
