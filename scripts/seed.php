<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */


//activate strict mode
declare(strict_types=1);

use tests\helper\TableCreator;
use Utilities\DbConnector;

//load composer dependencies
require __DIR__ . '/../vendor/autoload.php';

//load dotenv variables from '.env'
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

$_ENV['MYSQL_HOST'] = "172.21.0.1";

//get the database connection
$pdo = DbConnector::getConnection();

$script = $argv[1];

const PATH_TO_SQL = __DIR__ . "/../sql/seeds/";
function runSQL(PDO $pdo, string $name)
{
    $statements = file_get_contents(PATH_TO_SQL . $name . ".sql");
    $pdo->exec($statements);
}

if ($script === "reset") {
    $pdo->exec("DROP DATABASE " . $_ENV['MYSQL_DATABASE'] . ";");
    $pdo->exec("CREATE DATABASE " . $_ENV['MYSQL_DATABASE'] . ";");
    $pdo->exec("use " . $_ENV['MYSQL_DATABASE'] . ";");
    TableCreator::create($pdo);
    runSQL($pdo, "3roles");
} else {
    runSQL($pdo, $script);
}
