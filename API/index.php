<?php

//activate strict mode
declare(strict_types=1);

//load composer dependencies
require 'vendor/autoload.php';

use BenSauer\CaseStudySkygateApi\DatabaseController\DatabaseConnector;
use BenSauer\CaseStudySkygateApi\DatabaseController\DatabaseCreator;
use BenSauer\CaseStudySkygateApi\DatabaseController\DatabaseSeeder;

//load dotenv variables from '.env'
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

header("HTTP/1.1 400");
exit();

//connect to the Database
//DatabaseConnector::connect();

//create database tables if they not exists
//DatabaseCreator::create(); //TODO remove later

//seed database with roles and admin
//DatabaseSeeder::seed(["roles","admin"]); //TODO remove later
