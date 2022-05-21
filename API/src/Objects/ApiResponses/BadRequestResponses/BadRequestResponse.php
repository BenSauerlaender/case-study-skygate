<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\ApiResponses\BadRequestResponses;

use BenSauer\CaseStudySkygateApi\Objects\ApiResponses\BaseResponse;

/**
 * Response that should be used if the request was bad.
 */
class BadRequestResponse extends BaseResponse
{
    public function __construct(string $msg, int $errorCode, array $info = [])
    {
        $this->setCode(400);
        if (!empty($info)) {
            $this->setData($info);
        }
        $this->addErrorCode($errorCode);
        $this->addMessage($msg);
    }
}
