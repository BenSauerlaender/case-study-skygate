<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\DbAccessors;

use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlRoleAccessor;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\RoleNotFoundException;

/**
 * Test class for the MySqlRoleAccessor 
 */
final class MySqlRoleAccessorTest extends BaseMySqlAccessorTest
{
    private ?RoleAccessorInterface $accessor;

    public function setUp(): void
    {
        self::resetDB();

        //creates a role
        self::$pdo->exec('
            INSERT INTO role
                (name,permissions)
            VALUES 
                ("test","perm123");
        ');

        $this->startChangedRowsObservation();

        //initialize the EcrAccessor
        $this->accessor = new MySqlRoleAccessor(self::$pdo);
    }

    /**
     * Tests if the method returns null if there is no role with this name
     */
    public function testFindByNameReturnsNull(): void
    {
        $response = $this->accessor->findByName("someName");
        $this->assertNull($response);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method returns the correct id
     */
    public function testFindByNameSuccessful(): void
    {
        $response = $this->accessor->findByName("test");
        $this->assertEquals(1, $response);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method throws the correct error if the role with this id do not exists
     */
    public function testGetWithWrongID(): void
    {
        $this->expectException(RoleNotFoundException::class);

        $this->accessor->get(11);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method throws the correct error if the role with this id do not exists
     */
    public function testGetSuccessful(): void
    {

        $response = $this->accessor->get(1);

        $this->assertArrayHasKey("createdAt", $response);
        $this->assertArrayHasKey("updatedAt", $response);

        $this->assertEquals("test", $response["name"]);
        $this->assertEquals("perm123", $response["permissions"]);
        $this->assertEquals(1, $response["id"]);

        $this->assertChangedRowsEquals(0);
    }

    public function testGetList(): void
    {
        $response = $this->accessor->getList();
        $this->assertEquals(["test"], $response);
        $this->assertChangedRowsEquals(0);

        self::$pdo->exec('
            INSERT INTO role
                (name)
            VALUES 
                ("admin"),
                ("user");
        ');

        $response = $this->accessor->getList();
        $this->assertEquals(["admin", "test", "user"], $response);

        self::resetDB();

        $response = $this->accessor->getList();
        $this->assertEquals([], $response);
    }
}
