<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\DatabaseUtilities\Accessors;

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller\MySqlTableCreator;
use BenSauer\CaseStudySkygateApi\tests\UnitTests\DatabaseUtilities\BaseDatabaseTest;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Base class for all MySqlAccessor tests
 * 
 * Handles the database connection
 */
abstract class BaseMySqlAccessorTest extends BaseDatabaseTest
{
    /**
     * The UNIX timestamp at the start of observation
     *
     * @var int|null
     */
    private static ?int $start = null;

    /**
     * Deletes and re-creates the database and tables
     */
    protected static function resetDB(): void
    {
        self::$pdo->exec("DROP DATABASE " . $_ENV['DB_DATABASE'] . ";");
        self::$pdo->exec("CREATE DATABASE " . $_ENV['DB_DATABASE'] . ";");
        self::$pdo->exec("use " . $_ENV['DB_DATABASE'] . ";");

        //create tables
        MySqlTableCreator::create(self::$pdo);
    }

    /**
     * SetUps the assertChangedRowsEquals function
     */
    protected function startChangedRowsObservation(): void
    {
        //save the timestamp
        self::$start = time();

        //TODO change this:
        sleep(1);
    }

    /**
     * Counts changed rows and performs an "assertEquals" on the expected and actual result
     * 
     * @param int $expected The expected number of changed rows
     */
    protected function assertChangedRowsEquals(int $expected): void
    {
        //all the tables to observe
        $tables = ["user", "role", "emailChangeRequest"];

        $changedRows = 0;

        //going over each table
        foreach ($tables as $table) {

            $stmt = self::$pdo->query(
                'SELECT updated_at FROM ' . $table . ';'
            );

            //going over each row
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                //if the row was updated after the observation start
                if (strtotime($row["updated_at"]) !== self::$start) {
                    $changedRows++;
                }
            }
        }

        $this->assertEquals($expected, $changedRows);
    }
}
