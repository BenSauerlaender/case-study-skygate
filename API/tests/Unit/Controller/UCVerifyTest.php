<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller;

use BadMethodCallException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use InvalidArgumentException;
use OutOfRangeException;

/**
 * Testsuit for UserController->verifyUser method
 */
final class UCVerifyTest extends BaseUCTest
{
    /**
     * Tests if the method throws an exception if the user is not in the database
     */
    public function testVerifyUserNotExists(): void
    {
        // userAccessor-> get will return always null.
        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->will($this->throwException(new UserNotFoundException("User: 1")));

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("1");

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

        $return = $this->userController->verifyUser(1, "ABC1");
        $this->assertEquals(false, $return);
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

        $return = $this->userController->verifyUser(1, "ABC");
        $this->assertEquals(true, $return);
    }
}
