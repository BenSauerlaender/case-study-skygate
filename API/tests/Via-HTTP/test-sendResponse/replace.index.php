<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\Utilities\RouterUtilities;

try {
    //load composer dependencies
    require '../vendor/autoload.php';


    //load dotenv variables from '.env'
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
    $dotenv->load();

    //get SimpleResponse and SimpleResponseCookie
    require 'helper.php';

    $response = new SimpleResponse();

    //get the correct test from the url
    $test = explode("/", $_SERVER["REQUEST_URI"])[2];

    //config the response according to the test
    switch ($test) {
        case "testResponseCode200":
            $response->setCode(200);
            break;
        case "testResponseCode401":
            $response->setCode(401);
            break;
        case "testResponseCode500":
            $response->setCode(500);
            break;
        case "testHeader":
            $response->addHeader("test-header", "test-value");
            break;
        case "testTwoHeaders":
            $response->addHeader("test-header", "test-value");
            $response->addHeader("test-header2", "test-value2");
            break;
        case "testCookie":
            break;
    }

    //call the function to test
    RouterUtilities::sendResponse($response);
    exit();
} catch (Exception $e) {
    error_log($e->getMessage());
    header($_SERVER["SERVER_PROTOCOL"] . ' 500 Internal Server Error', true, 500);
    exit();
}
