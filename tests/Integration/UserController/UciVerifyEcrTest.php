<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Integration\UserController;

use Exceptions\DBExceptions\FieldNotFoundExceptions\EcrNotFoundException;

/**
 * Integration Tests for the verifyUsersEmailChange method of UserController
 */
final class UciVerifyEcrTest extends BaseUCITest
{
    /**
     * Tests if verifyUsersEmailChange throws exception no request found
     */
    public function testVerifyEcrNotFound(): void
    {
        $this->expectException(EcrNotFoundException::class);
        $this->userController->verifyUsersEmailChange(1, "code");
    }

    /**
     * Tests if verifyUsersEmailChange throws exception if the code is wrong
     */
    public function testVerifyEcrFailsOnIncorrectCode(): void
    {
        $this->createUserWithEcr();

        $response = $this->userController->verifyUsersEmailChange(1, "code");
        $this->assertFalse($response);
    }

    /**
     * Tests if verifyUsersEmailChange throws exception if the code is wrong
     */
    public function testVerifyEcr(): void
    {
        $code = $this->createUserWithEcr();

        $response = $this->userController->verifyUsersEmailChange(1, $code);
        $this->assertTrue($response);
    }
}
