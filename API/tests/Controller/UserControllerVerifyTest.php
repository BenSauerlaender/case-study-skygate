<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\Controller\UserController;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\EcrAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidAttributeException;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\ValidatorInterface;
use BenSauer\CaseStudySkygateApi\Utilities\SecurityUtilities;
use PHPUnit\Framework\TestCase;

/**
 * Testsuit for UserController->verifyUser method
 */
final class UserControllerVerifyTest extends TestCase
{
    /**
     * Tests if the method throws an exception if the id is < 0
     */
    public function testVerifyUserIDOutOfRange(): void
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

        $uc->verifyUser(-1, "");
    }

    /**
     * Tests if the method throws an exception if the user is not in the database
     */
    public function testVerifyUserNotExists(): void
    {
        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);

        // userAccessor-> get will return always null.
        $userAcc->expects($this->once())
            ->method("get")
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
        $uc->verifyUser(1, "");
    }

    /**
     * Tests if the method throws an exception if the user is already verified
     */
    public function testVerifyUserIsVerifiedAlready(): void
    {
        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);

        // userAccessor-> get will return a verified user
        $userAcc->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(["verified" => true]);

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $this->expectException(BadMethodCallException::class);
        $uc->verifyUser(1, "");
    }


    /**
     * Tests if the method throws an exception if the code is incorrect
     */
    public function testVerifyUserWithWrongCode(): void
    {
        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);

        $userAcc->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(["verified" => false, "verificationCode" => "ABC"]);


        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $this->expectException(InvalidArgumentException::class);
        $uc->verifyUser(1, "ABC1");
    }

    /**
     * Tests if the method calls all functions correctly
     */
    public function testVerifyUserSuccessful(): void
    {
        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);

        $userAcc->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn(["verified" => false, "verificationCode" => "ABC"]);

        $userAcc->expects($this->once())
            ->method("update")
            ->with($this->equalTo(1, ["verificationCode" => false, "verified" => true]));

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $uc->verifyUser(1, "ABC");
    }
}
