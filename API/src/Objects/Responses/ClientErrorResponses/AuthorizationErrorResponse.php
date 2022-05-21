<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses;

use BenSauer\CaseStudySkygateApi\Objects\Responses\BaseResponse;

/**
 * Response that should be used if the request cant be authorized or has not required permissions
 */
class AuthorizationErrorResponse extends BaseResponse
{
    public function __construct(string $msg, int $errorCode)
    {
        $this->setCode(401);
        $this->addErrorCode($errorCode);
        $this->addMessage($msg);
    }
}
