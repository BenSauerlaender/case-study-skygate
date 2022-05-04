<?php

//activate strict mode
declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\RequestBuilder;
use BenSauer\CaseStudySkygateApi\Router\RouterBuilder;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\RecourseNotFoundResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\NotSecureResponse;
use BenSauer\CaseStudySkygateApi\Utilities\ApiUtilities;

try {
    //load composer dependencies
    require '../vendor/autoload.php';

    //load dotenv variables from '.env'
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    //check for correct version
    if (!str_starts_with($_SERVER["Request_URI"], $PATH_PREFIX)) {

        $response = new RecourseNotFoundResponse();

        //check for ssl connection
    } else if ($_ENV["ENVIRONMENT"] === "PRODUCTION" and ($_SERVER["HTTPS"] !== "")) {

        $response = new NotSecureResponse();
    } else {

        $router = RouterBuilder::build();
        $handler = $router->route($apiPath, $method);

        $Request = RequestBuilder::build($_SERVER["Request_URI"], $_COOKIE, $PATH_PREFIX);

        $response = $handler->handle($Request);
    }

    ApiUtilities::sendResponse($response);
    exit();
} catch (Exception $e) {
    error_log($e->getMessage());
    header_remove();
    http_response_code(500);
    exit();
}
