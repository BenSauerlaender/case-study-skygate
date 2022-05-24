<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Controller\UserController;

use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\RoleNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidPropertyException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\MissingPropertiesException;

/**
 * Test suite for UserController->createUser method
 */
final class UCCreateTest extends BaseUCTest
{

    /**
     * Tests if the method throws an Exception if at least one property is missing
     * 
     * @dataProvider incompleteAttributeProvider
     */
    public function testCreateUserWithMissingAttributes(array $properties): void
    {

        $this->expectException(MissingPropertiesException::class);

        $this->userController->createUser($properties);
    }

    /**
     * Provides different incomplete properties arrays
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
     * Tests if the method throw an exception if at least one property is invalid
     */
    public function testCreateUserWithInvalidproperties(): void
    {
        $this->ValidationControllerMock->method("validate")->willReturn(["email" => ["TO_SHORT"]]);

        $this->expectException(InvalidPropertyException::class);
        $this->userController->createUser(self::$completeAttr);
    }

    /**
     * Tests if the method throws an exception if the email is already in use in at least one of the relevant tables
     * 
     * @dataProvider \BenSauer\CaseStudySkygateApi\tests\Unit\Controller\UserController\Provider::NANDProvider()
     */
    public function testCreateUserWithDuplicateEmail(bool $emailFreeInUser, bool $emailFreeInEcr): void
    {
        //ValidationController validates everything
        $this->ValidationControllerMock->expects($this->once())
            ->method("validate")
            ->willReturn(true);

        $this->configEmailAvailability($emailFreeInUser, $emailFreeInEcr);

        $this->expectException(InvalidPropertyException::class);

        $this->userController->createUser(self::$completeAttr);
    }

    /**
     * Tests if the method throws an exception if the role cant be found
     */
    public function testCreateUserWithInvalidRole(): void
    {
        //ValidationController validates everything
        $this->ValidationControllerMock->expects($this->once())
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
    public function testCreateUserSuccessful(array $inputproperties, array $expectValidated): void
    {

        //the userID for the new created user
        $returnedUserID = 11;

        //ValidationController validates everything
        $this->ValidationControllerMock->expects($this->once())
            ->method("validate")
            ->with($this->equalTo($expectValidated))
            ->willReturn(true);

        //user Accessor cant find the email at the first time - bc they is not in use. But can find it at second time.
        //and then returns userID = 11
        $this->userAccessorMock->expects($this->exactly(2))
            ->method("findByEmail")
            ->withConsecutive([$this->equalTo($inputproperties["email"])], [$this->equalTo($inputproperties["email"])])
            ->willReturnOnConsecutiveCalls(null, $returnedUserID);

        //ECR Accessor cant find the email - bc they is not in use
        $this->ecrAccessorMock->expects($this->once())
            ->method("findByEmail")
            ->with($this->equalTo($inputproperties["email"]));

        // if role is not specified it will use "user"
        $expectedRole = $inputproperties["role"] ?? "user";
        $this->roleAccessorMock->expects($this->once())
            ->method("findByName")
            ->with($this->equalTo($expectedRole))
            ->willReturn(0);

        // return the hash "hash"
        $this->SecurityControllerMock->expects($this->once())
            ->method("hashPassword")
            ->with($this->equalTo($inputproperties["password"]))
            ->willReturn("hash");

        //generate code "ABC"
        $this->SecurityControllerMock->expects($this->once())
            ->method("generateCode")
            ->with($this->equalTo(10))
            ->willReturn("ABC");

        //expect the right data to insert into the DB
        $this->userAccessorMock->expects($this->once())
            ->method("insert")
            ->with($this->equalTo(
                $inputproperties["email"],
                $inputproperties["name"],
                $inputproperties["postcode"],
                $inputproperties["city"],
                $inputproperties["phone"],
                "hash",
                "false",
                $expectedRole
            ));

        $result = $this->userController->createUser($inputproperties);
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
     * Example property array with all attributes
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
