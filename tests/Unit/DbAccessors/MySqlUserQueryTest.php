<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Unit\DbAccessors;

use BadMethodCallException;
use DbAccessors\Interfaces\UserAccessorInterface;
use DbAccessors\Interfaces\UserQueryInterface;
use DbAccessors\MySqlUserAccessor;
use DbAccessors\MySqlUserQuery;
use Exceptions\DBExceptions\FieldNotFoundExceptions\RoleNotFoundException;
use Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use Exceptions\DBExceptions\UniqueFieldExceptions\DuplicateEmailException;
use Exceptions\ValidationExceptions\ArrayIsEmptyException;
use Exceptions\ValidationExceptions\InvalidPropertyException;
use PDO;

/**
 * Test class for the MySqlUserQuery
 */
final class MySqlUserQueryTest extends BaseMySqlAccessorTest
{
    private ?UserQueryInterface $query;

    public function setUp(): void
    {
        self::resetDB();

        //creates a role
        self::$pdo->exec('INSERT INTO role (name) VALUES ("test"),("test2");');

        //creates 100 users
        $seed = file_get_contents(__DIR__ . "/../../../sql/seeds/100Users.sql");
        self::$pdo->exec($seed);

        $this->startChangedRowsObservation();

        //initialize the UserQuery
        $this->query = new MySqlUserQuery(self::$pdo);
    }

    /**
     * Tests if the addFilter function throws an exception if the property is invalid
     */
    public function testAddFilterFailsOnWrongField(): void
    {
        $this->expectException(InvalidPropertyException::class);

        $this->query->addFilter("quatsch", "jo");

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the addFilter function throws an exception if the match is invalid
     */
    public function testAddFilterFailsOnWrongMatch1(): void
    {
        $this->expectException(InvalidPropertyException::class);

        $this->query->addFilter("name", "%jo");

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the addFilter function throws an exception if the match is invalid
     */
    public function testAddFilterFailsOnWrongMatch2(): void
    {
        $this->expectException(InvalidPropertyException::class);

        $this->query->addFilter("name", "jo_");

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the addFilter function throws an exception if the match is invalid
     */
    public function testSetSortFailsOnWrongField(): void
    {
        $this->expectException(InvalidPropertyException::class);

        $this->query->setSort("quatsch");

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the query returns all entries
     */
    public function testSearchAll(): void
    {
        $result = $this->query->getResults();

        $this->assertEquals(100, sizeOf($result));

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the query filters caseSensitive correctly
     */
    public function testSearchFilteredCaseSensitive(): void
    {
        $this->query->addFilter("city", "se", true);
        $result = $this->query->getResults();

        $this->assertEquals(11, sizeOf($result));

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the query filters caseInsensitive correctly
     */
    public function testSearchFilteredCaseInsensitive(): void
    {
        $this->query->addFilter("city", "se", false);
        $result = $this->query->getResults();

        $this->assertEquals(12, sizeOf($result));

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the query filters correctly if 2 filters a given
     */
    public function testSearch2Filtered(): void
    {
        $this->query->addFilter("city", "se", false);
        $this->query->addFilter("name", "W", true);
        $result = $this->query->getResults();

        $this->assertEquals(3, sizeOf($result));

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the query sorts correctly ascending
     */
    public function testSortACS(): void
    {
        $this->query->setSort("postcode");
        $result = $this->query->getResults();

        $this->assertEquals(100, sizeOf($result));

        $this->assertEquals("Jonathan Bührmann", $result[0]["name"]);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the query sorts correctly decreasing
     */
    public function testSortDESC(): void
    {
        $this->query->setSort("email", false);
        $result = $this->query->getResults();

        $this->assertEquals(100, sizeOf($result));

        $this->assertEquals("54552", $result[0]["postcode"]);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the query returns the correct length
     */
    public function testGetLength1(): void
    {
        $result = $this->query->getLength();

        $this->assertEquals(100, $result);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the query returns the correct length
     */
    public function testGetLength2(): void
    {
        $this->query->addFilter("city", "se", false);
        $this->query->addFilter("name", "W", true);

        $result = $this->query->getLength();

        $this->assertEquals(3, $result);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the query returns the correct pagination
     */
    public function testGetPagination(): void
    {
        $result = $this->query->getResultsPaginated(10, 0);
        $this->assertEquals(10, sizeOf($result));

        $result = $this->query->getResultsPaginated(60, 1);
        $this->assertEquals(40, sizeOf($result));

        $result = $this->query->getResultsPaginated(10, 11);
        $this->assertEquals(0, sizeOf($result));

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the query returns the correct results
     */
    public function testCombination(): void
    {
        $this->query->addFilter("city", "se", false);
        $this->query->setSort("phone", false);

        $result = $this->query->getLength();
        $this->assertEquals(12, $result);

        $result = $this->query->getResults();
        $this->assertEquals(95, $result[0]["id"]);
        $this->assertEquals(12, sizeOf($result));

        $result = $this->query->getResultsPaginated(3, 0);
        $this->assertEquals(95, $result[0]["id"]);
        $this->assertEquals(3, sizeOf($result));

        $result = $this->query->getResultsPaginated(3, 2);
        $this->assertEquals(46, $result[0]["id"]);
        $this->assertEquals(3, sizeOf($result));

        $this->assertChangedRowsEquals(0);
    }
}
