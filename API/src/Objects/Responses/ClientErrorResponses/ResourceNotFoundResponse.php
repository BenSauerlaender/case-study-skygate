<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses;

use BenSauer\CaseStudySkygateApi\Objects\Responses\BaseResponse;
use Exception;

/**
 * Response that should be used if the resource cant be found
 */
class ResourceNotFoundResponse extends BaseResponse
{
    public function __construct(Exception $e)
    {
        $this->setCode(404);
        $this->addMessage("The resource can't be found. " . $e->getMessage());
    }
}
