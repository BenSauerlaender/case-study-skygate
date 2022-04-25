<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\RoleNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\UniqueFieldExceptions\DuplicateEmailException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidFieldException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidTypeException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\RequiredFieldException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\UnsupportedFieldException;

/**
 * Testsuit for UserController->createUser method
 */
final class UCCreateTest extends BaseUCTest
{

    /**
     * Tests if the method throws an Exception if at least one field is missing
     * 
     * @dataProvider incompleteAttributeProvider
     */
    public function testCreateUserWithMissingAttributes(array $fields): void
    {

        $this->expectException(RequiredFieldException::class);

        $this->userController->createUser($fields);
    }

    /**
     * Provides different incomplete fields arrays
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
     * Tests if the method throw an exception if at least one field is unsupported
     */
    public function testCreateUserWithUnsupportedFields(): void
    {
        $this->validatorMock->method("validate")->will($this->throwException(new UnsupportedFieldException()));

        $this->expectException(UnsupportedFieldException::class);
        $this->userController->createUser(self::$completeAttr);
    }

    /**
     * Tests if the method throw an exception if at least one field has the wrong type
     */
    public function testCreateUserWithInvalidTypeFields(): void
    {
        $this->validatorMock->method("validate")->will($this->throwException(new InvalidTypeException()));

        $this->expectException(InvalidTypeException::class);
        $this->userController->createUser(self::$completeAttr);
    }

    /**
     * Tests if the method throw an exception if at least one field is invalid
     */
    public function testCreateUserWithInvalidFields(): void
    {
        $this->validatorMock->method("validate")->willReturn(["email" => "TO_SHORT"]);

        $this->expectException(InvalidFieldException::class);
        $this->userController->createUser(self::$completeAttr);
    }

    /**
     * Tests if the method throws an exception if the email is already in use in at least one of the relevant tables
     * 
     * @dataProvider \BenSauer\CaseStudySkygateApi\tests\Unit\Controller\Provider::NANDProvider()
     */
    public function testCreateUserWithDuplicateEmail(bool $emailFreeInUser, bool $emailFreeInEcr): void
    {
        //validator validates everything
        $this->validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn(true);

        $this->configEmailAvailability($emailFreeInUser, $emailFreeInEcr);

        $this->expectException(DuplicateEmailException::class);

        $this->userController->createUser(self::$completeAttr);
    }

    /**
     * Tests if the method throws an exception if the role cant be found
     */
    public function testCreateUserWithInvalidRole(): void
    {
        //validator validates everything
        $this->validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn(true);

        //config mocks so that the email is free
        $this->configEmailAvailability(true, true);

        //config mock so that the role can't be found
        $this->roleAccessorMock->method("findByName")->willReturn(null);

        $this->expectException(RoleNotFoundException::class);

        $this->userController->createUser(self::$completeAttr);
    }

    /**
     * Tests if everything goes well and all dependencies are called correct
     *  
     * @dataProvider goodAttributesProvider
     */
    public function testCreateUserSuccessful(array $inputFields, array $expectValidated): void
    {

        //the userID for the new created user
        $returnedUserID = 11;

        //validator validates everything
        $this->validatorMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo($expectValidated))
            ->willReturn(true);

        //user Accessor cant find the email at the first time - bc they is not in use. But can find it at second time.
        //and then returns userID = 11
        $this->userAccessorMock->expects($this->exactly(2))
            ->method("findByEmail")
            ->withConsecutive([$this->equalTo($inputFields["email"])], [$this->equalTo($inputFields["email"])])
            ->willReturnOnConsecutiveCalls(null, $returnedUserID);

        //ECR Accessor cant find the email - bc they is not in use
        $this->ecrAccessorMock->expects($this->once())
            ->method("findByEmail")
            ->with($this->equalTo($inputFields["email"]));

        // if role is not specified it will use "user"
        $expectedRole = $inputFields["role"] ?? "user";
        $this->roleAccessorMock->expects($this->once())
            ->method("findByName")
            ->with($this->equalTo($expectedRole))
            ->willReturn(0);

        // return the hash "hash"
        $this->securityUtilitiesMock->expects($this->once())
            ->method("hashPassword")
            ->with($this->equalTo($inputFields["password"]))
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
                $inputFields["email"],
                $inputFields["name"],
                $inputFields["postcode"],
                $inputFields["city"],
                $inputFields["phone"],
                "hash",
                "false",
                $expectedRole
            ));

        $result = $this->userController->createUser($inputFields);
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
     * Example field array with all attributes
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
