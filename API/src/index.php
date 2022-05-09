<?php

//activate strict mode
declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\NotSecureResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\ResourceNotFoundResponse;
use BenSauer\CaseStudySkygateApi\Utilities\ApiUtilities;

try {
    //load composer dependencies
    require '../vendor/autoload.php';

    //load dotenv variables from '.env'
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    //check for correct path syntax
    if (!str_starts_with($_SERVER["Request_URI"], $PATH_PREFIX)) {

        $response = new ResourceNotFoundResponse();
    }
    //check for ssl connection
    else if ($_ENV["ENVIRONMENT"] === "PRODUCTION" and ($_SERVER["HTTPS"] !== "")) {

        $response = new NotSecureResponse();
    }
    //normal procedure
    else {

        //get the constructed apiController
        $apiController = ApiUtilities::getApiController();

        //get the request
        $request = ApiUtilities::getRequest($_SERVER, getallheaders(), $PATH_PREFIX);

        //get the response
        $response = $apiController->handle($request);
    }

    //send the response
    ApiUtilities::sendResponse($response);

    exit();
}
//catch all completely unexpected exceptions
catch (Exception $e) {

    //log them
    error_log($e->getMessage());

    //send a 500 internal server error
    header_remove();
    http_response_code(500);
    exit();
}
