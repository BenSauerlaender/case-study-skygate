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
 * Testsuit for UserController->createUser method
 */
final class UserControllerCreateTest extends TestCase
{

    /**
     * Example attribute array with all attributes
     *
     * @var array<string,string>
     */
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
     * Tests if the method returns an Exception if at least one attribute is missing
     * 
     * @dataProvider incompleteAttributeProvider
     */
    public function testCreateUserWithoutAllAttributes(array $attr): void
    {
        //create the user controller with stub dependencies
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

    /**
     * Provides different incomplete attribute arrays
     */
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

    /**
     * Tests if the method throws an exception if at least one attribute is invalid
     */
    public function testCreateUserWithInvalidAttributes(): void
    {
        //the validator will always throw the exception
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
     * Tests if the method throws an exception if the email is already in use in at least one of the relevant tables
     * 
     * @dataProvider duplicateEmailProvider
     */
    public function testCreateUserWithDuplicateEmail(bool $emailInUser, bool $emailInEcr): void
    {
        //create stubs so that the email is already in use in at least one of the tables.

        $stubUserAcc = $this->createStub(UserAccessorInterface::class);
        if ($emailInUser) {
            //email in use
            $stubUserAcc->method("findByEmail")->willReturn(0);
        } else {
            //email not found
            $stubUserAcc->method("findByEmail")->willReturn(null);
        }

        $stubEcrAcc = $this->createStub(EcrAccessorInterface::class);
        if ($emailInEcr) {
            //email in use
            $stubEcrAcc->method("findByEmail")->willReturn(0);
        } else {
            //email not found
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

    /**
     * Tests if the method throws an exception if the role cant be found
     */
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
     * Tests if everything goes well if nothing goes wrong
     * 
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

        //validator validates everything
        $validator->expects($this->once())
            ->method("validate")
            ->with($this->equalTo($expectValidated));

        //user Accessor cant find the email at the first time - so they is not in use. But can find it at second time.
        //and then returns userID = 11
        $userAcc->expects($this->exactly(2))
            ->method("findByEmail")
            ->withConsecutive([$this->equalTo($inputAttr["email"])], [$this->equalTo($inputAttr["email"])])
            ->willReturnOnConsecutiveCalls(null, 11);


        //ECR Accessor cant find the email - so they is not in use
        $ecrAcc->expects($this->once())
            ->method("findByEmail")
            ->with($this->equalTo($inputAttr["email"]));

        // if role is not specified it will use "user"
        $expectedRole = $inputAttr["role"] ?? "user";
        $roleAcc->expects($this->once())
            ->method("findByName")
            ->with($this->equalTo($expectedRole))
            ->willReturn(0);

        // return the hash "hash"
        $secUtil->expects($this->once())
            ->method("hashPassword")
            ->with($this->equalTo($inputAttr["password"]))
            ->willReturn("hash");

        //generate code "ABC"
        $secUtil->expects($this->once())
            ->method("generateCode")
            ->with($this->equalTo(10))
            ->willReturn("ABC");

        //expect the right data to insert into the DB
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
