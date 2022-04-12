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
 * Testsuit for UserController->requestUsersEmailChange method
 */
final class UserControllerRequestEmailTest extends TestCase
{
    /**
     * Tests if the method throws an exception if the id is < 0
     */
    public function testRequestEmailWithIDOutOfRange(): void
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

        $uc->requestUsersEmailChange(-1, "");
    }

    /**
     * Tests if the method throws an exception if the user is not in the database
     */
    public function testRequestEmailUserNotExists(): void
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
        $uc->requestUsersEmailChange(1, "");
    }

    /**
     * Tests if the method throws an exception if the Email is invalid
     */
    public function testRequestEmailWithInvalidEmail(): void
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
            ->willReturn([]);

        $validator->method("validate")
            ->will($this->throwException(new InvalidAttributeException));

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $this->expectException(InvalidAttributeException::class);
        $uc->requestUsersEmailChange(1, "email");
    }


    /**
     * Tests if the method throws an exception if the Email is not free
     * 
     * @dataProvider emailNotFreeProvider
     */
    public function testRequestEmailWithNotFreeEmail($userAcc, $ecrAcc): void
    {
        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);

        $userAcc->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn([]);

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $this->expectException(InvalidAttributeException::class);
        $uc->requestUsersEmailChange(1, "email");
    }

    /**
     * provides with accessor mocks so that at least in one table the email is not free
     */
    public function emailNotFreeProvider(): array
    {
        $u1 = $this->createMock(UserAccessorInterface::class);
        $e1 = $this->createMock(EcrAccessorInterface::class);
        $u2 = $this->createMock(UserAccessorInterface::class);
        $e2 = $this->createMock(EcrAccessorInterface::class);
        $u3 = $this->createMock(UserAccessorInterface::class);
        $e3 = $this->createMock(EcrAccessorInterface::class);

        $u1->method("findByEmail")->willReturn(0);
        $e1->method("findByEmail")->willReturn(null);
        $u2->method("findByEmail")->willReturn(0);
        $e2->method("findByEmail")->willReturn(null);
        $u3->method("findByEmail")->willReturn(0);
        $e3->method("findByEmail")->willReturn(0);

        return [
            [$u1, $e1],
            [$u2, $e2],
            [$u3, $e3]
        ];
    }


    /**
     * Tests if the method calls all functions correct
     */
    public function testRequestEmailSuccessful(): void
    {
        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);

        // userAccessor-> get will return always 0.
        $userAcc->expects($this->once())
            ->method("get")
            ->with($this->equalTo(1))
            ->willReturn([]);

        $validator->expects($this->once())
            ->method("validate")
            ->with($this->equalTo(["email" => "email"]));

        $ecrAcc->expects($this->once())
            ->method("findByEmail")
            ->with($this->equalTo("email"))
            ->willReturn(null);
        $userAcc->expects($this->once())
            ->method("findByEmail")
            ->with($this->equalTo("email"))
            ->willReturn(null);

        $ecrAcc->expects($this->once())
            ->method("deleteByUserID")
            ->with($this->equalTo(1));

        $secUtil->expects($this->once())
            ->method("generateCode")
            ->with($this->equalTo(10))
            ->willReturn("code");

        $ecrAcc->expects($this->once())
            ->method("insert")
            ->with($this->equalTo(1, "email", "code"));

        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $code = $uc->requestUsersEmailChange(1, "email");
        $this->assertEquals("code", $code);
    }
}
