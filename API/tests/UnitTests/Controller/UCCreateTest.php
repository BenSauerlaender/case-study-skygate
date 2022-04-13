<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\Controller;

use BenSauer\CaseStudySkygateApi\Exceptions\InvalidAttributeException;
use InvalidArgumentException;

/**
 * Testsuit for UserController->createUser method
 */
final class UCCreateTest extends BaseUCTest
{

    /**
     * Tests if the method throws an Exception if at least one attribute is missing
     * 
     * @dataProvider incompleteAttributeProvider
     */
    public function testCreateUserWithMissingAttributes(array $attr): void
    {

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("missing attributes");

        $this->userController->createUser($attr);
    }

    /**
     * Provides different incomplete attribute arrays
     */
    public function incompleteAttributeProvider(): array
    {
        return [
            "empty array" => [
                []
            ],
            "only email" => [
                ["email" => "test@mail.de"]
            ],
            "without password" => [
                [
                    "email" => "test@mail.de",
                    "name" => "Ben Sauerländer",
                    "postcode" => "01234",
                    "city" => "Berlin",
                    "phone" => "030 12345-67"
                ]
            ]
        ];
    }

    /**
     * Tests if the method throw an exception if at least one attribute is invalid
     */
    public function testCreateUserWithInvalidAttributes(): void
    {
        //the validator will always throw invalidAttributeException
        $this->validatorMock->method("validate")->willThrowException(new InvalidAttributeException);

        $this->expectException(InvalidAttributeException::class);
        $this->userController->createUser(self::$completeAttr);
    }

    /**
     * Tests if the method throws an exception if the email is already in use in at least one of the relevant tables
     * 
     * @dataProvider \BenSauer\CaseStudySkygateApi\tests\UnitTests\Controller\Provider::NANDProvider()
     */
    public function testCreateUserWithDuplicateEmail(bool $emailFreeInUser, bool $emailFreeInEcr): void
    {
        $this->configEmailAvailability($emailFreeInUser, $emailFreeInEcr);

        $this->expectException(InvalidAttributeException::class);
        $this->expectExceptionCode(110);
        $this->expectExceptionMessage("is already in use");

        $this->userController->createUser(self::$completeAttr);
    }

    /**
     * Tests if the method throws an exception if the role cant be found
     */
    public function testCreateUserWithInvalidRole(): void
    {
        //config mocks so that the email is free
        $this->configEmailAvailability(true, true);

        //config mock so that the role can't be found
        $this->roleAccessorMock->method("findByName")->willReturn(null);

        $this->expectException(InvalidAttributeException::class);
        $this->expectExceptionCode(106);
        $this->expectExceptionMessage("is not a valid role");

        $this->userController->createUser(self::$completeAttr);
    }

    /**
     * Tests if everything goes well and all dependencies are called correct
     *  
     * @dataProvider goodAttributesProvider
     */
    public function testCreateUserSuccessful(array $inputAttr, array $expectValidated): void
    {

        //the userID for the new created user
        $returnedUserID = 11;

        //validator validates everything
        $this->validatorMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo($expectValidated));

        //user Accessor cant find the email at the first time - bc they is not in use. But can find it at second time.
        //and then returns userID = 11
        $this->userAccessorMock->expects($this->exactly(2))
            ->method("findByEmail")
            ->withConsecutive([$this->equalTo($inputAttr["email"])], [$this->equalTo($inputAttr["email"])])
            ->willReturnOnConsecutiveCalls(null, $returnedUserID);

        //ECR Accessor cant find the email - bc they is not in use
        $this->ecrAccessorMock->expects($this->once())
            ->method("findByEmail")
            ->with($this->equalTo($inputAttr["email"]));

        // if role is not specified it will use "user"
        $expectedRole = $inputAttr["role"] ?? "user";
        $this->roleAccessorMock->expects($this->once())
            ->method("findByName")
            ->with($this->equalTo($expectedRole))
            ->willReturn(0);

        // return the hash "hash"
        $this->securityUtilitiesMock->expects($this->once())
            ->method("hashPassword")
            ->with($this->equalTo($inputAttr["password"]))
            ->willReturn("hash");

        //generate code "ABC"
        $this->securityUtilitiesMock->expects($this->once())
            ->method("generateCode")
            ->with($this->equalTo(10))
            ->willReturn("ABC");

        //expect the right data to insert into the DB
        $this->userAccessorMock->expects($this->once())
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

        $result = $this->userController->createUser($inputAttr);
        $this->assertEquals(["id" => $returnedUserID, "verificationCode" => "ABC"], $result);
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

    /**
     * Example attribute array with all attributes
     *
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
}
