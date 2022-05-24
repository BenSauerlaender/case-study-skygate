<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\helper;

use PDO;

/**
 * Handles database table creation
 * 
 * @codeCoverageIgnore
 * 
 */
class TableCreator
{
    /**
     * Create all tables
     *
     * @param  PDO $pdo
     */
    static public function create(PDO $pdo, ?string $sqlPath = null): void
    {
        if (is_null($sqlPath)) {
            $sqlPath = self::PATH_TO_SQL;
        }

        //create all tables via SQL
        foreach (self::TABLES as $t) {
            $statements = file_get_contents($sqlPath . $t);
            $pdo->exec($statements);
        }
    }

    /**
     * relative Path to the sql-files
     */
    private const PATH_TO_SQL = __DIR__ . "/../../sql/tables/";

    /**
     * SQL-files with CREATE Table - Statements for each table
     */
    private const TABLES = ["role.sql", "user.sql", "emailChangeRequest.sql", "refreshToken.sql"];
}
