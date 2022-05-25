<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Objects\Cookies;

/**
 * Class to represent a Cookie that holds a refreshToken
 */
final class RefreshTokenCookie extends BaseCookie
{
    public function __construct(string $token)
    {
        parent::__construct("skygatecasestudy.refreshtoken", $token, 60 * 60 * 24 * 30, "/", true, true);
    }
}
