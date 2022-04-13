<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\Controller;

use BadMethodCallException;
use InvalidArgumentException;
use OutOfRangeException;

/**
 * Testsuit for UserController->verifyUser method
 */
final class UCVerifyTest extends BaseUCTest
{
    /**
     * Tests if the method throws an exception if the id is < 0
     */
    public function testVerifyUserIDOutOfRange(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("is not a valid id");

        $this->userController->verifyUser(-1, "");
    }

    /**
     * Tests if the method throws an exception if the user is not in the database
     */
    public function testVerifyUserNotExists(): void
    {
        // userAccessor-> get will return always null.
        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("There is no user");

        $this->userController->verifyUser(1, "");
    }

    /**
     * Tests if the method throws an exception if the user is already verified
     */
    public function testVerifyUserIsAlreadyVerified(): void
    {
        // userAccessor-> get will return a verified user
        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(["verified" => true]);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("is already verified");

        $this->userController->verifyUser(1, "");
    }


    /**
     * Tests if the method throws an exception if the code is incorrect
     */
    public function testVerifyUserWithWrongCode(): void
    {
        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(["verified" => false, "verificationCode" => "ABC"]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Verification code is not correct");

        $this->userController->verifyUser(1, "ABC1");
    }

    /**
     * Tests if everything goes well and all dependencies are called correct
     */
    public function testVerifyUserSuccessful(): void
    {
        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(["verified" => false, "verificationCode" => "ABC"]);

        $this->userAccessorMock->expects($this->once())
            ->method("update")
            ->with($this->equalTo(1, ["verificationCode" => false, "verified" => true]));

        $this->userController->verifyUser(1, "ABC");
    }
}
