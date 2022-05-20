<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Integration\UserController;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidPropertyException;

/**
 * Integration Tests for the requestUsersEmailChange method of UserController
 */
final class UciRequestEcrTest extends BaseUCITest
{
    /**
     * Tests if requestUsersEmailChange throws exception if user not found
     */
    public function testRequestEcrFailsOnInvalidUser(): void
    {
        $this->expectException(UserNotFoundException::class);
        $this->userController->requestUsersEmailChange(10, "myNewEmail@mail.de");
    }

    /**
     * Tests if requestUsersEmailChange throws exception if email not free
     */
    public function testRequestEcrFailsOnDuplicateEmail(): void
    {
        $this->create2Users();

        $this->expectException(InvalidPropertyException::class);
        $this->userController->requestUsersEmailChange(1, "yourEmail@mail.de");
    }

    /**
     * Tests if requestUsersEmailChange throws exception if email is invalid
     */
    public function testRequestEcrFailsOnInvalidEmail(): void
    {
        $this->createUser();

        $this->expectException(InvalidPropertyException::class);
        $this->userController->requestUsersEmailChange(1, "invalidEmail");
    }

    /**
     * Tests if requestUsersEmailChange works
     */
    public function testRequestEcr(): void
    {
        $this->createUser();
        $response = $this->userController->requestUsersEmailChange(1, "myNewEmail@mail.de");

        $this->assertIsString($response);
        $this->assertEquals(10, strlen($response));
    }
}
