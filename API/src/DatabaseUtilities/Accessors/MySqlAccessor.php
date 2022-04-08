<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Accessors;

// functions to interact with the emailChangeRequest-db-table
class MySqlAccessor
{
    protected \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
}
