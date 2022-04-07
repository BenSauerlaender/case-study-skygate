<?php

declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\Exceptions\DatabaseException;
use BenSauer\CaseStudySkygateApi\Exceptions\MissingDependencyException;
use BenSauer\CaseStudySkygateApi\Utilities\UserUtilities;
use PHPUnit\Framework\TestCase;

use BenSauer\CaseStudySkygateApi\tests\Mocks\MockEcrAccessor;
use BenSauer\CaseStudySkygateApi\tests\Mocks\MockRoleAccessor;
use BenSauer\CaseStudySkygateApi\tests\Mocks\MockUserAccessor;
use BenSauer\CaseStudySkygateApi\tests\Mocks\MockValidator;
use BenSauer\CaseStudySkygateApi\tests\Mocks\MockUserSearchQuery;

final class UserUtilitiesTest extends TestCase
{
    public function testCreateNewUserWithoutSetUp(): void
    {
        $this->expectException(MissingDependencyException::class);
        UserUtilities::createNewUser("", "", "", "", "", "", "");
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testCreateNewUserWithInvalidData(array $arg, int $code): void
    {
        UserUtilities::setUp(new MockEcrAccessor, new MockRoleAccessor, new MockUserAccessor, new MockValidator, new MockUserSearchQuery);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode($code);

        UserUtilities::createNewUser($arg[0], $arg[1], $arg[2], $arg[3], $arg[4], $arg[5], $arg[6]);
    }
    public function invalidDataProvider(): array
    {
        return [
            "invalid email" => [["false", "", "", "", "", "", ""], 100],
            "invalid name" => [["", "false", "", "", "", "", ""], 101],
            "invalid postcode" => [["", "", "false", "", "", "", ""], 102],
            "invalid city" => [["", "", "", "false", "", "", ""], 103],
            "invalid phone" => [["", "", "", "", "false", "", ""], 104],
            "invalid role" => [["", "", "", "", "", "false", ""], 106],
            "invalid password" => [["", "", "", "", "", "", "false"], 105],
            "duplicate email" => [["duplicate", "", "", "", "", "", ""], 110]
        ];
    }
    /**
     * @dataProvider invalidDataProvider
     */
    public function testCreateUserWithDBErrors(array $arg): void
    {
        UserUtilities::setUp(new MockEcrAccessor, new MockRoleAccessor, new MockUserAccessor, new MockValidator, new MockUserSearchQuery);
        $this->expectException(DatabaseException::class);
        UserUtilities::createNewUser($arg[0], $arg[1], $arg[2], $arg[3], $arg[4], $arg[5], $arg[6]);
    }

    public function testCreateNewUser(): void
    {
        UserUtilities::setUp(new MockEcrAccessor, new MockRoleAccessor, new MockUserAccessor, new MockValidator, new MockUserSearchQuery);


        $this->expectException(MissingDependencyException::class);
        UserUtilities::createNewUser("", "", "", "", "", "", "");
    }
}
