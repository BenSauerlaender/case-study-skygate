<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\Responses\SuccessfulResponses;

use Exception;
use BenSauer\CaseStudySkygateApi\Objects\Responses\BaseResponse;

/**
 * Response that should be used if a route processed successful and not data need to returned
 */
class NoContentResponse extends BaseResponse
{
    public function __construct()
    {
        $this->setCode(204);
    }
}
