<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Objects\Responses\ServerErrorResponses;

use Exception;
use Objects\Responses\BaseResponse;

/**
 * Response that should be used if an exception bubbles up
 */
class InternalErrorResponse extends BaseResponse
{
    public function __construct(?Exception $e = null)
    {
        $this->setCode(500);

        //add the exception stack to the response if there is one
        if (!is_null($e)) {
            $data["Exception"] = "$e";
            $this->setBody($data);
        }

        $this->addMessage("There are internal problems. Try again later or contact the support.");
    }
}
