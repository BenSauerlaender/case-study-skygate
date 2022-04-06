<?php

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\DatabaseUtilities\Traits;

trait DBconnectionTrait
{

    static private ?\PDO $db = null;

    //set up a connection via PDO
    static public function setDBConnection(\PDO $db): void
    {
        self::$db = $db;
    }

    //checks if connection is set up
    static private function isDBconnected(): bool
    {
        return is_null(self::$db);
    }
}
