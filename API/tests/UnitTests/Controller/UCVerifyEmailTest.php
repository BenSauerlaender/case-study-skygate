<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\Controller;

use InvalidArgumentException;
use OutOfRangeException;

/**
 * Testsuit for UserController->verifyUsersEmailChange method
 */
final class UCVerifyEmailTest extends BaseUCTest
{
    /**
     * Tests if the method throws an exception if the id is < 0
     */
    public function testVerifyEmailWithIDOutOfRange(): void
    {

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("is not a valid id");

        $this->userController->verifyUsersEmailChange(-1, "");
    }

    /**
     * Tests if the method throws an exception if the request is not in the database
     */
    public function testVerifyEmailRequestNotExists(): void
    {

        // will return always null.
        $this->ecrAccessorMock->expects($this->once())
            ->method("findByUserID")
            ->with($this->equalTo(1))
            ->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("There is no email change request");
        $this->userController->verifyUsersEmailChange(1, "");
    }

    /**
     * Tests if the method throws an exception if the code is incorrect
     */
    public function testVerifyEmailRequestWithIncorrectCode(): void
    {
        $this->ecrAccessorMock->expects($this->once())
            ->method("findByUserID")
            ->with($this->equalTo(1))
            ->willReturn(0);

        $this->ecrAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(0))
            ->willReturn(["verificationCode" => "code1"]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Verification code is incorrect");

        $this->userController->verifyUsersEmailChange(1, "code2");
    }

    /**
     * Tests if everything goes well and all dependencies are called correct
     */
    public function testVerifyEmailRequestSuccess(): void
    {
        $this->ecrAccessorMock->expects($this->once())
            ->method("findByUserID")
            ->with($this->equalTo(1))
            ->willReturn(0);

        $this->ecrAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(0))
            ->willReturn(["verificationCode" => "code1", "newEmail" => "email"]);

        $this->userAccessorMock->expects($this->once())
            ->method("update")
            ->with($this->equalTo(1, ["email" => "email"]));

        $this->ecrAccessorMock->expects($this->once())
            ->method("delete")
            ->with($this->equalTo(0));

        $this->userController->verifyUsersEmailChange(1, "code1");
    }
}
