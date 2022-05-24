<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\Responses\SuccessfulResponses;

use BenSauer\CaseStudySkygateApi\Objects\Cookies\Interfaces\CookieInterface;
use BenSauer\CaseStudySkygateApi\Objects\Responses\BaseResponse;

/**
 * Response that should be used if the request processed successful and a cookie should be set
 */
class SetCookieResponse extends NoContentResponse
{
    public function __construct(CookieInterface $cookie)
    {
        $this->addCookie($cookie);
        parent::__construct();
    }
}
