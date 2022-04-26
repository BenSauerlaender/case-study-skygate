<?php

//activate strict mode
declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\DatabaseUtilities\Controller\MySqlConnector;

//load composer dependencies
require './../../vendor/autoload.php';


//load dotenv variables from '.env'
$dotenv = Dotenv\Dotenv::createImmutable("./../../", ".env");
$dotenv->load();

//connect to the Database
MySqlConnector::getConnection();

//create database tables if they not exists
//DatabaseCreator::create(); //TODO remove later

//seed database with roles and admin
//DatabaseSeeder::seed(["roles","admin"]); //TODO remove later
