<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\Objects\Responses\BaseResponse;
use BenSauer\CaseStudySkygateApi\Utilities\ApiUtilities;

try {
    //load composer dependencies
    require '../vendor/autoload.php';

    //load dotenv variables from '.env'
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
    $dotenv->load();

    //get SimpleResponse and SimpleResponseCookie (Helper classes only for testing)
    require 'helper.php';

    $response = new SimpleResponse();

    //get the correct test from the url
    $test = explode("/", $_SERVER["REQUEST_URI"])[3];

    //config the response according to the test
    //test description in sendResponse.test.js
    switch ($test) {
        case "testResponseCode200":
            $response->setCode(200);
            break;
        case "testResponseCode204":
            $response->setCode(204);
            break;
        case "testHeader":
            $response->addHeader("Content-Type", "test-value");
            break;
        case "testTwoHeaders":
            $response->addHeader("Content-Type", "test-value");
            $response->addHeader("last-modified", "test-value2");
            break;
        case "testCookie":
            $cookie = new SimpleResponseCookie("cookie", "value", 60, "path", true, false);
            $response->addCookie($cookie);
            break;
        case "testCookie2":
            $cookie = new SimpleResponseCookie("cookie", "value", 0, "", false, true);
            $response->addCookie($cookie);
            break;
        case "testData":
            $response->setBody(["testData" => "test", "testObj" => ["num1" => 1, "num2" => 2]]);
            break;
        case "testAll":
            $response->setCode(200);
            $response->addHeader("Content-Type", "test-value");
            $response->addHeader("last-modified", "test-value2");
            $cookie = new SimpleResponseCookie("cookie", "value", 60, "path", true, false);
            $response->addCookie($cookie);
            $response->setBody(["testData" => "test", "testObj" => ["num1" => 1, "num2" => 2]]);
            break;
    }

    //call the function to test
    BaseResponse::send($response, "domain", "api/v1/");
    exit();
} catch (Exception $e) {
    error_log("$e");
    exit();
}
