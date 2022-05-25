<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Objects\Responses\ClientErrorResponses;

use Objects\Responses\BaseResponse;

/**
 * Response that should be used if the resource don't support the method
 */
class MethodNotAllowedResponse extends BaseResponse
{
    public function __construct(array $availableMethods)
    {
        $this->setCode(405);
        $this->setBody(["availableMethods" => $availableMethods]);
        $this->addMessage("The requested route don't allow the requested method.");
    }
}
