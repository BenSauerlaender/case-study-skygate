<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\Controller\UserController;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\EcrAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\ValidatorInterface;
use BenSauer\CaseStudySkygateApi\Utilities\SecurityUtilities;
use PHPUnit\Framework\TestCase;

/**
 * Testsuit for UserController->verifyUsersEmailChange method
 */
final class UserControllerVerifyEmailTest extends TestCase
{
    /**
     * Tests if the method throws an exception if the id is < 0
     */
    public function testVerifyEmailWithIDOutOfRange(): void
    {

        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $this->expectException(OutOfRangeException::class);

        $uc->verifyUsersEmailChange(-1, "");
    }

    /**
     * Tests if the method throws an exception if the request is not in the database
     */
    public function testVerifyEmailRequestNotExists(): void
    {
        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);

        // will return always null.
        $ecrAcc->expects($this->once())
            ->method("findByUserID")
            ->with($this->equalTo(1))
            ->willReturn(null);

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $this->expectException(InvalidArgumentException::class);
        $uc->verifyUsersEmailChange(1, "");
    }

    /**
     * Tests if the method throws an exception if the code is incorrect
     */
    public function testVerifyEmailRequestCodeIncorrect(): void
    {
        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);

        $ecrAcc->expects($this->once())
            ->method("findByUserID")
            ->with($this->equalTo(1))
            ->willReturn(0);

        $ecrAcc->expects($this->once())
            ->method("get")
            ->with($this->equalTo(0))
            ->willReturn(["verificationCode" => "code1"]);

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $this->expectException(InvalidArgumentException::class);
        $uc->verifyUsersEmailChange(1, "code2");
    }

    /**
     * Tests if the method calls all functions correct
     */
    public function testVerifyEmailRequestSuccess(): void
    {
        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);

        $ecrAcc->expects($this->once())
            ->method("findByUserID")
            ->with($this->equalTo(1))
            ->willReturn(0);

        $ecrAcc->expects($this->once())
            ->method("get")
            ->with($this->equalTo(0))
            ->willReturn(["verificationCode" => "code1", "newEmail" => "email"]);

        $userAcc->expects($this->once())
            ->method("update")
            ->with($this->equalTo(1, ["email" => "email"]));

        $ecrAcc->expects($this->once())
            ->method("delete")
            ->with($this->equalTo(0));


        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $uc->verifyUsersEmailChange(1, "code1");
    }
}
