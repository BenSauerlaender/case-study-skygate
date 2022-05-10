<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses;

/**
 * Response that should be used if an exception bubbles up
 */
class InternalErrorResponse extends BaseResponse
{
    public function __construct(string $msg = "There are internal problems. Try again later or contact the support.")
    {
        $this->setCode(500);
        $this->setMessage($msg);
    }
}
