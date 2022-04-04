<?php

//activate strict mode
declare(strict_types = 1);

//load composer dependencies
require 'vendor/autoload.php';
use BenSauer\CaseStudySkygateApi\DatabaseController\DatabaseConnector;

//load dotenv variables from '.env'
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

DatabaseConnector::connect();
DatabaseConnector::getConnection();

?> 