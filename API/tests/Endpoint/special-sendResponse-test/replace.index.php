<?php

//activate strict mode
declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\tests\Unit\Router\Response\mockResponse;
use BenSauer\CaseStudySkygateApi\Utilities\RouterUtilities;

//load composer dependencies
require './../../vendor/autoload.php';

//load dotenv variables from '.env'
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../", ".env");
$dotenv->load();

$response = new mockResponse();

$response->setCode(400);

RouterUtilities::sendResponse($response);
exit();
