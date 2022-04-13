<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\DatabaseUtilities\Accessors\EcrAccessor;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\Interfaces\EcrAccessorInterface;
use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors\MySqlEcrAccessor;
use BenSauer\CaseStudySkygateApi\tests\UnitTests\DatabaseUtilities\Accessors\BaseMySqlAccessorTest;
use InvalidArgumentException;

/**
 * Base class for all MySqlAccessor tests
 * 
 * Handles the database connection
 */
final class ECRAccFindByUserIDTest extends BaseMySqlAccessorTest
{
    private ?EcrAccessorInterface $accessor;

    public function setUp(): void
    {
        self::resetDB();

        //creates 2 users
        self::$pdo->exec('
            INSERT INTO user
                (email, name, postcode, city, phone, hashed_pass, verified, role_id)
            VALUES 
                ("user0@mail.de","user0","00000","admintown","015937839",1,true,0),
                ("user1@mail.de","user1","00000","admintown","015937839",1,true,0);
        ');

        //creates 2 requests
        self::$pdo->exec('
            INSERT INTO emailChangeRequest 
                (user_id,new_email,verification_code)
            VALUES
                (1,"newEmailfor1","code"),
                (0,"newEmailfor0","code2");
        ');

        //initialize the EcrAccessor
        $this->accessor = new MySqlEcrAccessor(self::$pdo);
    }

    /**
     * Tests if the method returns null if the userID don't exists
     */
    public function testFindByUserIDWhenEcrNotExists(): void
    {
        $response = $this->accessor->findByUserID(3);
        $this->assertNull($response);

        $this->assertChangedRowsEquals(0);
    }

    /**
     * Tests if the method returns the correct id
     * 
     * @dataProvider successProvider
     */
    public function testFindByUserIDSuccessful(int $userID, int $ecrID): void
    {
        $response = $this->accessor->findByUserID($userID);
        $this->assertEquals($ecrID, $response);

        $this->assertChangedRowsEquals(0);
    }

    public static function successProvider(): array
    {
        return [
            [1, 0],
            [0, 1]
        ];
    }
}
