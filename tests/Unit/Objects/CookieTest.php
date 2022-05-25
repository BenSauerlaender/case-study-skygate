<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Unit\Objects;

use Objects\Cookies\RefreshTokenCookie;
use PHPUnit\Framework\TestCase;

/**
 * Tests for all Cookie classes
 */
final class CookieTest extends TestCase
{
    /**
     * Tests if the refreshTokenCookie returns the correct data
     */
    public function testRefreshTokenCookie(): void
    {
        $cookie = new RefreshTokenCookie("toooken");

        $this->assertEquals("skygatecasestudy.refreshtoken", $cookie->getName());
        $this->assertEquals([
            "name"      => "skygatecasestudy.refreshtoken",
            "value"     => "toooken",
            "expiresIn" => 60 * 60 * 24 * 30,
            "path"      => "/",
            "secure"    => true,
            "httpOnly"  => true
        ], $cookie->get());
    }
}
