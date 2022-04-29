<?php

//activate strict mode
declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\Router\Requests\RequestBuilder;
use BenSauer\CaseStudySkygateApi\Router\RouterBuilder;
use BenSauer\CaseStudySkygateApi\Router\Responses\RecourseNotFoundResponse;
use BenSauer\CaseStudySkygateApi\Router\Responses\NotSecureResponse;
use BenSauer\CaseStudySkygateApi\Utilities\RouterUtilities;

try {
    //load composer dependencies
    require './../../vendor/autoload.php';

    //load dotenv variables from '.env'
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../", ".env");
    $dotenv->load();

    $PATH_PREFIX = "/api/v1";

    //check for correct version
    if (!str_starts_with($_SERVER["REQUEST_URI"], $PATH_PREFIX)) {

        $response = new RecourseNotFoundResponse();

        //check for ssl connection
    } else if ($_ENV["ENVIRONMENT"] === "PRODUCTION" and ($_SERVER["HTTPS"] !== "")) {

        $response = new NotSecureResponse();
    } else {

        $router = RouterBuilder::build();
        $handler = $router->route($apiPath, $method);

        $request = RequestBuilder::build($_SERVER["REQUEST_URI"], $_COOKIE, $PATH_PREFIX);

        $response = $handler->handle($request);
    }

    RouterUtilities::sendResponse($response);
    exit();
} catch (Exception $e) {
    error_log($e->getMessage());
    header($_SERVER["SERVER_PROTOCOL"] . ' 500 Internal Server Error', true, 500);
    exit();
}
