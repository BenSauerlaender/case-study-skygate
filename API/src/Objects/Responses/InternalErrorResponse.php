<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\Responses;

use Exception;

/**
 * Response that should be used if an exception bubbles up
 */
class InternalErrorResponse extends BaseResponse
{
    public function __construct(?Exception $e = null)
    {
        $this->setCode(500);
        if (!is_null($e)) $data["Exception"] = "$e";
        $this->setData($data);
        $this->addMessage("There are internal problems. Try again later or contact the support.");
    }
}
