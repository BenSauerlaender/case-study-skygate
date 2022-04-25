<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\EcrNotFoundException;
use InvalidArgumentException;
use OutOfRangeException;

/**
 * Testsuit for UserController->verifyUsersEmailChange method
 */
final class UCVerifyEmailTest extends BaseUCTest
{
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

        $this->expectException(EcrNotFoundException::class);
        $this->expectExceptionMessage("1");
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

        $return = $this->userController->verifyUsersEmailChange(1, "code2");
        $this->assertEquals(false, $return);
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

        $return = $this->userController->verifyUsersEmailChange(1, "code1");
        $this->assertEquals(true, $return);
    }
}
