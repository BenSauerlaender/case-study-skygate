<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\ApiResponses;

/**
 * Response that should be used if the accessToken is expired
 */
class AccessTokenExpiredResponse extends BaseResponse
{
    public function __construct()
    {
        $this->setCode(401);
        $this->addMessage("The JWT Access Token is expired. Use GET /token to get a new one one");
    }
}
