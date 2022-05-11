<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Scripts;

use BenSauer\CaseStudySkygateApi\Utilities\MySqlConnector;
use BenSauer\CaseStudySkygateApi\Utilities\MySqlTableCreator;
use Dotenv\Dotenv;

use function PHPUnit\Framework\arrayHasKey;

require __DIR__ . '/../../vendor/autoload.php';

//load dotenv variables from '.env'
$dotenv = Dotenv::createImmutable(__DIR__ . "/../..");
$dotenv->load();

$pdo = MySqlConnector::getConnection();

$availableSeeds = [
    "3roles" => '
            INSERT INTO role
                (name)
            VALUES 
                ("user"),
                ("admin"),
                ("test");
        ',
];
foreach ($argv as $arg) {
    if (array_key_exists($arg, $availableSeeds)) {
        $pdo->exec($availableSeeds[$arg]);
        echo "seeded: $arg\n";
    }
}
