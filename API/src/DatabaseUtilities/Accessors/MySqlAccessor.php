<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors;

/**
 * Super class for all MySql accessors
 * 
 * Provides a PDO object to interact with the database.
 */
class MySqlAccessor
{
    /**
     * PDO object for database interaction
     */
    protected \PDO $pdo;

    /**
     * Sets the PDO object
     *
     * @param  \PDO $pdo PDO object for database interaction
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
}
