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
 * Response that should be used if the request processed successful and data should be returned
 */
class DataResponse extends BaseResponse
{
    public function __construct(array $data)
    {
        $this->setCode(200);
        $this->setBody($data);
    }
}
