<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Integration\UserController;

use Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use Exceptions\ValidationExceptions\InvalidPropertyException;

/**
 * Integration Tests for the updatePassword method of UserController
 */
final class UciUpdatePassTest extends BaseUCITest
{
    /**
     * Tests if updatePassword throws exception if user not found
     */
    public function testUpdatePassFailsOnInvalidUser(): void
    {
        $this->expectException(UserNotFoundException::class);
        $this->userController->updateUsersPassword(10, "new", "old");
    }

    /**
     * Tests if updatePassword throws exception if the new password is invalid
     */
    public function testUpdatePassFailsOnInvalidPass(): void
    {
        $this->createUser();

        $this->expectException(InvalidPropertyException::class);
        $this->userController->updateUsersPassword(1, "incorrect", "MyPassword1");
    }

    /**
     * Tests if updatePassword returns false if the old password is incorrect
     */
    public function testUpdatePassFailsOnIncorrectPass(): void
    {
        $this->createUser();

        $ret = $this->userController->updateUsersPassword(1, "MyPassword2", "notMyPassword");
        $this->assertFalse($ret);
    }

    /**
     * Tests updatePassword 
     */
    public function testUpdatePass(): void
    {
        $this->createUser();

        $ret = $this->userController->updateUsersPassword(1, "MyPassword2", "MyPassword1");
        $this->assertTrue($ret);
    }
}
