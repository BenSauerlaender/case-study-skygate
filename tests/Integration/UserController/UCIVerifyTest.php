<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Integration\UserController;

use BadMethodCallException;
use Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;

/**
 * Integration Tests for the verifyUser method of UserController
 */
final class UCIVerifyTest extends BaseUCITest
{
    /**
     * test if verifyUser returns false if the code is wrong.
     */
    public function testVerifyFailsOnWrongUser(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->userController->verifyUser(101, "123456");
    }

    /**
     * test if verifyUser returns false if the code is wrong.
     */
    public function testVerifyFailsOnWrongCode(): void
    {
        $this->createUser();

        $ret = $this->userController->verifyUser(1, "123456");
        $this->assertFalse($ret);
    }

    /**
     * test to verify a just created user.
     */
    public function testVerifyFirstUser(): void
    {
        $code = $this->createUser();

        $ret = $this->userController->verifyUser(1, $code);
        $this->assertTrue($ret);
    }

    /**
     * test if verifyUser returns false if the code is wrong.
     */
    public function testVerifyFailsOnSameUser(): void
    {
        $code = $this->createUser();

        $this->expectException(BadMethodCallException::class);

        $this->userController->verifyUser(1, $code);
        $this->userController->verifyUser(1, $code);
    }
}
