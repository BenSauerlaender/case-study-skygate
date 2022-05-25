<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace tests\Unit\DbAccessors;

use DbAccessors\Interfaces\RefreshTokenAccessorInterface;
use DbAccessors\MySqlRefreshTokenAccessor;
use PDO;

/**
 * Test class for the MySqlRefreshTokenAccessor
 */
final class MySqlRefreshTokenAccessorTest extends BaseMySqlAccessorTest
{
    private ?RefreshTokenAccessorInterface $accessor;

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

        //creates 2 users
        self::$pdo->exec('
            INSERT INTO user
                (email, name, postcode, city, phone, hashed_pass, verified, role_id)
            VALUES 
                ("user0@mail.de","user0","00000","admintown","015937839",1,true,1),
                ("user1@mail.de","user1","00000","admintown","015937839",1,true,1)
        ');

        //creates refreshToken for user 1
        self::$pdo->exec('
            INSERT INTO refreshToken 
                (user_id,count)
            VALUES
                (1,13)
        ');

        $this->startChangedRowsObservation();

        //initialize the RefreshTokenAccessor
        $this->accessor = new MySqlRefreshTokenAccessor(self::$pdo);
    }

    /**
     * Tests if the method returns null if there is no entry for this userID
     */
    public function testGetCountByUserIDWhenEntryNotExist(): void
    {
        $response = $this->accessor->getCountByUserID(3);
        $this->assertNull($response);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method returns the correct count
     * 
     */
    public function testGetCountByUserIDSuccessful(): void
    {
        $response = $this->accessor->getCountByUserID(1);
        $this->assertEquals(13, $response);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method creates an new entry if not exists
     */
    public function testIncreaseWhenEntryNotExists(): void
    {
        $ret = $this->accessor->increaseCount(2);
        $this->assertEquals(0, $ret);

        $this->assertChangedRowsEquals(1);

        $row = self::$pdo->query('
            SELECT count 
            FROM refreshToken
            WHERE user_id=2
        ')->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEquals([["count" => 0]], $row);
    }

    /**
     * Tests if the method increases the count
     */
    public function testIncreaseSuccessful(): void
    {
        $this->accessor->increaseCount(1);

        $this->assertChangedRowsEquals(1);

        $row = self::$pdo->query('
            SELECT count 
            FROM refreshToken
            WHERE user_id=1
        ')->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEquals([["count" => 14]], $row);
    }
}
