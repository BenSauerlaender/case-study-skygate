<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\BadRequestResponses;

use BenSauer\CaseStudySkygateApi\Objects\Responses\BaseResponse;

/**
 * Response that should be used if the request was bad.
 */
class BadRequestResponse extends BaseResponse
{
    /**
     * @param  string $msg          The message to send.
     * @param  int    $errorCode    The Error-code specify what about the request is bad.
     * @param  int    $info         Additional information for the client. will be send in the body
     */
    public function __construct(string $msg, int $errorCode, array $info = [])
    {
        $this->setCode(400);

        if (!empty($info)) {
            $this->setBody($info);
        }

        $this->addErrorCode($errorCode);
        $this->addMessage($msg);
    }
}
