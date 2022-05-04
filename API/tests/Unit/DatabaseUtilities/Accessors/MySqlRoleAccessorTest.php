<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\DatabaseUtilities\Accessors\EcrAccessor;

use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\RoleAccessorInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\MySqlRoleAccessor;
use BenSauer\CaseStudySkygateApi\tests\Unit\DatabaseUtilities\Accessors\BaseMySqlAccessorTest;

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
                (name)
            VALUES 
                ("test");
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
}
