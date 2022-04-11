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

final class UserControllerTest extends TestCase
{

    private static array $completeAttr = [
        "email" => "test@mail.de",
        "name" => "Ben Sauerländer",
        "postcode" => "01234",
        "city" => "Berlin",
        "phone" => "030 12345-67",
        "password" => "1SicheresPassword",
        "role" => "admin"
    ];

    /**
     * @dataProvider incompleteAttributeProvider
     */
    public function testCreateUserWithoutAllAttributes(array $attr): void
    {
        $uc = new UserController(
            $this->createStub(SecurityUtilities::class),
            $this->createStub(ValidatorInterface::class),
            $this->createStub(UserAccessorInterface::class),
            $this->createStub(RoleAccessorInterface::class),
            $this->createStub(EcrAccessorInterface::class)
        );

        $this->expectException(InvalidArgumentException::class);

        $uc->createUser($attr);
    }

    public function incompleteAttributeProvider(): array
    {
        return [
            [[]],
            [["email" => "test@mail.de"]],
            [[
                "email" => "test@mail.de",
                "name" => "Ben Sauerländer",
                "postcode" => "01234",
                "city" => "Berlin",
                "phone" => "030 12345-67"
            ]]
        ];
    }

    public function testCreateUserWithInvalidAttributes(): void
    {
        $stubValidator = $this->createStub(ValidatorInterface::class);
        $stubValidator->method("validate")->willThrowException(new InvalidAttributeException);

        $uc = new UserController(
            $this->createStub(SecurityUtilities::class),
            $stubValidator,
            $this->createStub(UserAccessorInterface::class),
            $this->createStub(RoleAccessorInterface::class),
            $this->createStub(EcrAccessorInterface::class)
        );

        $this->expectException(InvalidAttributeException::class);

        $uc->createUser(self::$completeAttr);
    }

    /**
     * @dataProvider duplicateEmailProvider
     */
    public function testCreateUserWithDuplicateEmail(bool $emailInUser, bool $emailInEcr): void
    {
        //create stubs so that the email is already in use in at least one of the tables.

        $stubUserAcc = $this->createStub(UserAccessorInterface::class);
        if ($emailInUser) {
            $stubUserAcc->method("findByEmail")->willReturn(0);
        } else {
            $stubUserAcc->method("findByEmail")->willReturn(null);
        }

        $stubEcrAcc = $this->createStub(EcrAccessorInterface::class);
        if ($emailInEcr) {
            $stubEcrAcc->method("findByEmail")->willReturn(0);
        } else {
            $stubEcrAcc->method("findByEmail")->willReturn(null);
        }

        $uc = new UserController(
            $this->createStub(SecurityUtilities::class),
            $this->createStub(ValidatorInterface::class),
            $stubUserAcc,
            $this->createStub(RoleAccessorInterface::class),
            $stubEcrAcc
        );

        $this->expectException(InvalidAttributeException::class);
        $this->expectExceptionCode(110);

        $uc->createUser(self::$completeAttr);
    }

    public function duplicateEmailProvider(): array
    {
        return [
            [true, true],
            [true, false],
            [false, true],
        ];
    }

    public function testCreateUserWithInvalidRole(): void
    {
        //create stubs so that the email is free
        $stubUserAcc = $this->createStub(UserAccessorInterface::class);
        $stubUserAcc->method("findByEmail")->willReturn(null);
        $stubEcrAcc = $this->createStub(EcrAccessorInterface::class);
        $stubEcrAcc->method("findByEmail")->willReturn(null);

        //create stub so that the role can't be found
        $stubRoleAcc = $this->createStub(RoleAccessorInterface::class);
        $stubRoleAcc->method("findByName")->willReturn(null);


        $uc = new UserController(
            $this->createStub(SecurityUtilities::class),
            $this->createStub(ValidatorInterface::class),
            $stubUserAcc,
            $stubRoleAcc,
            $stubEcrAcc
        );

        $this->expectException(InvalidAttributeException::class);
        $this->expectExceptionCode(106);

        $uc->createUser(self::$completeAttr);
    }

    /**
     * @dataProvider goodAttributesProvider
     */
    public function testCreateUserSuccessfully(array $inputAttr, array $expectValidated): void
    {
        //create all mocks
        $secUtil = $this->createMock(SecurityUtilities::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $userAcc = $this->createMock(UserAccessorInterface::class);
        $roleAcc = $this->createMock(RoleAccessorInterface::class);
        $ecrAcc = $this->createMock(EcrAccessorInterface::class);

        $validator->expects($this->once())
            ->method("validate")
            ->with($this->equalTo($expectValidated));

        $userAcc->expects($this->exactly(2))
            ->method("findByEmail")
            ->withConsecutive([$this->equalTo($inputAttr["email"])], [$this->equalTo($inputAttr["email"])])
            ->willReturnOnConsecutiveCalls(null, 11);


        $ecrAcc->expects($this->once())
            ->method("findByEmail")
            ->with($this->equalTo($inputAttr["email"]));

        $expectedRole = $inputAttr["role"] ?? "user";
        $roleAcc->expects($this->once())
            ->method("findByName")
            ->with($this->equalTo($expectedRole))
            ->willReturn(0);

        $secUtil->expects($this->once())
            ->method("hashPassword")
            ->with($this->equalTo($inputAttr["password"]))
            ->willReturn("hash");

        $secUtil->expects($this->once())
            ->method("generateCode")
            ->with($this->equalTo(10))
            ->willReturn("ABC");

        $userAcc->expects($this->once())
            ->method("insert")
            ->with($this->equalTo(
                $inputAttr["email"],
                $inputAttr["name"],
                $inputAttr["postcode"],
                $inputAttr["city"],
                $inputAttr["phone"],
                "hash",
                "false",
                $expectedRole
            ));



        $uc = new UserController(
            $secUtil,
            $validator,
            $userAcc,
            $roleAcc,
            $ecrAcc,
        );

        $result = $uc->createUser($inputAttr);
        $this->assertEquals(["id" => 11, "verificationCode" => "ABC"], $result);
    }

    public function goodAttributesProvider(): array
    {
        return [
            "with role" => [
                [
                    "email" => "test@mail.de",
                    "name" => "Ben Sauerländer",
                    "postcode" => "01234",
                    "city" => "Berlin",
                    "phone" => "030 12345-67",
                    "password" => "1SicheresPassword",
                    "role" => "admin"
                ], [

                    "email" => "test@mail.de",
                    "name" => "Ben Sauerländer",
                    "postcode" => "01234",
                    "city" => "Berlin",
                    "phone" => "030 12345-67",
                    "password" => "1SicheresPassword",
                ]
            ],
            "without role" => [
                [
                    "email" => "test@mail.de",
                    "name" => "Ben Sauerländer",
                    "postcode" => "01234",
                    "city" => "Berlin",
                    "phone" => "030 12345-67",
                    "password" => "1SicheresPassword",
                ], [

                    "email" => "test@mail.de",
                    "name" => "Ben Sauerländer",
                    "postcode" => "01234",
                    "city" => "Berlin",
                    "phone" => "030 12345-67",
                    "password" => "1SicheresPassword",
                ]
            ]
        ];
    }
}
