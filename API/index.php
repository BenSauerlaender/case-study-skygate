<?php

//load composer dependencies
require __DIR__ . 'vendor/autoload.php';
use BenSauer\CaseStudySkygateApi;

//load dotenv variables from '.env'
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

?> 