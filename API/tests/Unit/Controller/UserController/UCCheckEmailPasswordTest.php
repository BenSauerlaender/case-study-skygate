<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller\UserController;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\EcrNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;

/**
 * Testsuit for UserController->checkEmailPassword method
 */
final class UCCheckEmailPasswordTest extends BaseUCTest
{
    /**
     * Tests if the method throws an exception if the user is not in the database
     */
    public function testCheckEmailPasswordWithUserNotExists(): void
    {
        // will return always null.
        $this->userAccessorMock->expects($this->once())
            ->method("findByEmail")
            ->with("email")
            ->willReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->userController->checkEmailPassword("email", "");
    }

    /**
     * Tests if the method returns false if the credentials dont match
     */
    public function testCheckEmailPasswordDontMatch(): void
    {
        $this->userAccessorMock->expects($this->once())
            ->method("findByEmail")
            ->with("")
            ->willReturn(0);

        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with(0)
            ->willReturn(["hashedPass" => "123"]);

        $this->SecurityControllerMock->expects($this->once())
            ->method("checkPassword")
            ->with("pass", "123")
            ->willReturn(false);

        $return = $this->userController->checkEmailPassword("", "pass");
        $this->assertEquals(false, $return);
    }

    /**
     * Tests if the method returns true if the credentials match
     */
    public function testCheckEmailPasswordMatch(): void
    {
        $this->userAccessorMock->expects($this->once())
            ->method("findByEmail")
            ->with("email")
            ->willReturn(0);

        $this->userAccessorMock->expects($this->once())
            ->method("get")
            ->with(0)
            ->willReturn(["hashedPass" => "123"]);

        $this->SecurityControllerMock->expects($this->once())
            ->method("checkPassword")
            ->with("pass", "123")
            ->willReturn(true);

        $return = $this->userController->checkEmailPassword("email", "pass");
        $this->assertEquals(true, $return);
    }
}
