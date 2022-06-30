<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Unit\Controller\UserController;

use Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use Exceptions\ValidationExceptions\InvalidPropertyException;

/**
 * Test suite for UserController->updateUsersPasswordPrivileged method
 */
final class UCUpdatePasswordPrivilegedTest extends BaseUCTest
{
    /**
     * Tests if the method throws an exception if new Password is invalid
     */
    public function testUpdatePasswordPrivilegedWithInvalidPass(): void
    {
        //validation will fail
        $this->ValidationControllerMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["password" => "newPass"]))
            ->willReturn(["password" => ["TO_SHORT"]]);

        $this->expectException(InvalidPropertyException::class);
        $this->userController->updateUsersPasswordPrivileged(1, "newPass");
    }

    /**
     * Tests if the method throws an exception if the user is not in the database
     */
    public function testUpdatePasswordPrivilegedUserNotExists(): void
    {
        $this->ValidationControllerMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["password" => "newPass"]))
            ->willReturn(true);

        $this->SecurityControllerMock->expects($this->once())
            ->method("hashPassword")
            ->with($this->equalTo("newPass"))
            ->willReturn("newHash");

        $this->userAccessorMock->expects($this->once())
            ->method("update")
            ->will($this->throwException(new UserNotFoundException(1)));

        $this->expectException(UserNotFoundException::class);
        $this->userController->updateUsersPasswordPrivileged(1, "newPass");
    }
    /**
     * Tests if everything goes well and all dependencies are called correct
     */
    public function testUpdatePasswordPrivilegedSuccessful(): void
    {

        $this->ValidationControllerMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["password" => "newPass"]))
            ->willReturn(true);

        $this->SecurityControllerMock->expects($this->once())
            ->method("hashPassword")
            ->with($this->equalTo("newPass"))
            ->willReturn("newHash");

        $this->userAccessorMock->expects($this->once())
            ->method("update")
            ->with($this->equalTo(1, ["hashedPass" => "newHash"]));

        $this->userController->updateUsersPasswordPrivileged(1, "newPass");
    }
}
