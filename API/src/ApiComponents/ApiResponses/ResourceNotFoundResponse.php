<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses;

/**
 * Response that should be used if the resource cant be found
 */
class ResourceNotFoundResponse extends BaseResponse
{
    public function __construct()
    {
        $this->setCode(404);
        $this->addMessage("The resource can't be found.");
    }
}
