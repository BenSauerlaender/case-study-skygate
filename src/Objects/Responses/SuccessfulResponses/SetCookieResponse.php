<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Objects\Responses\SuccessfulResponses;

use Objects\Cookies\Interfaces\CookieInterface;

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
