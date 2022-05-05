<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\DbAccessors;

use BadMethodCallException;
use BenSauer\CaseStudySkygateApi\tests\Database\BaseDatabaseTest;
use BenSauer\CaseStudySkygateApi\Utilities\MySqlTableCreator;
use InvalidArgumentException;
use PDO;

/**
 * Base class for all MySqlAccessor tests
 * 
 * Handles the database connection and provides functionality to observe db rows
 */
abstract class BaseMySqlAccessorTest extends BaseDatabaseTest
{

    /**
     * All tables to observe
     *
     * @var array<string>
     */
    private static array $tables = ["user", "role", "emailChangeRequest"];

    /**
     * An Array that contains an Array for each table, where all created_at fields are stored
     *
     * @var array<string,array<string>>
     */
    private static ?array $snapshot = [];

    /**
     * Deletes and re-creates the database and tables
     */
    protected static function resetDB(): void
    {
        self::$pdo->exec("DROP DATABASE " . $_ENV['MYSQL_DATABASE'] . ";");
        self::$pdo->exec("CREATE DATABASE " . $_ENV['MYSQL_DATABASE'] . ";");
        self::$pdo->exec("use " . $_ENV['MYSQL_DATABASE'] . ";");

        //create tables
        MySqlTableCreator::create(self::$pdo);
    }

    /**
     * SetUps the assertChangedRowsEquals function
     */
    protected function startChangedRowsObservation(): void
    {

        foreach (self::$tables as $table) {

            $stmt = self::$pdo->query(
                'SELECT created_at FROM ' . $table . ';'
            );

            //fill $snapshot with created_at fields 
            self::$snapshot[$table] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
    }

    /**
     * Counts changed rows and performs an "assertEquals" on the expected and actual result
     * 
     * @param int $expected The expected number of changed rows
     */
    protected function assertChangedRowsEquals(int $expected): void
    {
        if (sizeof(self::$snapshot) === 0) throw new BadMethodCallException("The Observation has not been started");


        $changedRows = 0;

        //going over each table
        foreach (self::$tables as $table) {

            $stmt = self::$pdo->query(
                'SELECT created_at, updated_at FROM ' . $table . ';'
            );

            //going over each row
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                //if the row is in the snapshot
                if (in_array($row["created_at"], self::$snapshot[$table])) {
                    $this->deleteOneElementFromSnapshot($table, $row["created_at"]);

                    //if the row was updated
                    if ($row["created_at"] !== $row["updated_at"]) {
                        $changedRows++;
                    }
                } else {
                    $changedRows++;
                }
            }
            //Add the number of deleted rows, since the snapshot was created
            $changedRows += sizeof(self::$snapshot[$table]);
        }

        $this->assertEquals($expected, $changedRows);
    }

    private function deleteOneElementFromSnapshot(string $table, string $date): void
    {
        $idx = 9999;
        for ($i = 0; $i < sizeof(self::$snapshot[$table]); $i++) {
            if (self::$snapshot[$table][$i] === $date) {
                $idx = $i;
            }
        }
        if ($idx === 9999) throw new InvalidArgumentException("The date: " . $date . " cant be found in the snapshot of table: " . $table);
        //delete element at index $idx
        array_splice(self::$snapshot[$table], $idx, 1);
    }
}
