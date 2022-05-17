<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses;

use Exception;

/**
 * Response that should be used if a new entry was created and not data need to returned
 */
class CreatedResponse extends BaseResponse
{
    public function __construct()
    {
        $this->setCode(201);
    }
}
