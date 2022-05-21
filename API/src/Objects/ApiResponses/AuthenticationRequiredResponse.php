<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\ApiResponses;

/**
 * Response that should be used if the route requires authentication, which is not given
 */
class AuthenticationRequiredResponse extends BaseResponse
{
    public function __construct()
    {
        $this->setCode(401);
        $this->addMessage("The resource with this method require an JWT Access Token as barrier token. Use GET /token to get one");
    }
}
