<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\Responses;

use BenSauer\CaseStudySkygateApi\Objects\Responses\Interfaces\ResponseCookieInterface;

/**
 * Response that should be used if the request processed successful and a cookie should be set
 */
class SetCookieResponse extends BaseResponse
{
    public function __construct(ResponseCookieInterface $cookie)
    {
        $this->setCode(204);
        $this->addCookie($cookie);
    }
}
