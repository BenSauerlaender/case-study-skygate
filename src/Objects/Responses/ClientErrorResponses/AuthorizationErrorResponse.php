<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Objects\Responses\ClientErrorResponses;

use Objects\Responses\BaseResponse;

/**
 * Response that should be used if the request cant be authorized or has not required permissions
 */
class AuthorizationErrorResponse extends BaseResponse
{
    /**
     * @param  string $msg          The message to send.
     * @param  int    $errorCode    The Error-code specify what error accrued.
     */
    public function __construct(string $msg, int $errorCode)
    {
        $this->setCode(401);
        $this->addErrorCode($errorCode);
        $this->addMessage($msg);
    }
}
