<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Objects\Responses\SuccessfulResponses;

use Exception;
use Objects\Responses\BaseResponse;

/**
 * Response that should be used if a route processed successful and no body need to returned
 */
class NoContentResponse extends BaseResponse
{
    public function __construct()
    {
        $this->setCode(204);
    }
}
