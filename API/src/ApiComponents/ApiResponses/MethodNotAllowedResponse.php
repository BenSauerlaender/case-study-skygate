<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses;

/**
 * Response that should be used if the resource don't support the method
 */
class MethodNotAllowedResponse extends BaseResponse
{
    public function __construct(array $availableMethods)
    {
        $this->setCode(405);
        $this->setData(["msg" => "The Resource don't allow this method.", "availableMethods" => $availableMethods]);
    }
}
