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

require __DIR__ . '/../../vendor/autoload.php';

//load dotenv variables from '.env'
$dotenv = Dotenv::createImmutable(__DIR__ . "/../..");
$dotenv->load();

$pdo = MySqlConnector::getConnection();
self::$pdo->exec("DROP DATABASE " . $_ENV['MYSQL_DATABASE'] . ";");
self::$pdo->exec("CREATE DATABASE " . $_ENV['MYSQL_DATABASE'] . ";");
self::$pdo->exec("use " . $_ENV['MYSQL_DATABASE'] . ";");

//create tables
MySqlTableCreator::create(self::$pdo);
