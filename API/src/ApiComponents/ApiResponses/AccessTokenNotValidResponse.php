<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses;

/**
 * Response that should be used if the accessToken is invalid
 */
class AccessTokenNotValidResponse extends BaseResponse
{
    public function __construct()
    {
        $this->setCode(401);
        $this->setData(["msg" => "The JWT Access Token is not valid. Use GET /token to get a new one one"]);
    }
}
