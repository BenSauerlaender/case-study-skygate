<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses;

use BenSauer\CaseStudySkygateApi\Objects\Responses\BaseResponse;

class NotSecureResponse extends BaseResponse
{
    public function __construct()
    {
        $this->setCode(400);
        $this->addMessage("Request was rejected, because the connection is not secured via SSL (HTTPS). Please send your request again, via HTTPS.");
    }
}
