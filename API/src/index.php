<?php

//activate strict mode
declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\InternalErrorResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\NotSecureResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\ResourceNotFoundResponse;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiHeaderException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiMethodException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiPathException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiQueryException;
use BenSauer\CaseStudySkygateApi\Exceptions\NotSecureException;
use BenSauer\CaseStudySkygateApi\Utilities\ApiUtilities;

try {
    //load composer dependencies
    require '../vendor/autoload.php';

    //load dotenv variables from '.env'
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
    $dotenv->load();

    try {
        //get the request
        $request = ApiUtilities::getRequest($_SERVER, getallheaders(), $_ENV["API_PATH_PREFIX"], file_get_contents('php://input'));

        //get the constructed apiController
        $apiController = ApiUtilities::getApiController();

        //get the response
        $response = $apiController->handleRequest($request);
    } catch (NotSecureException $e) {
        $response = new NotSecureResponse();
    } catch (InvalidApiPathException $e) {
        $response = new ResourceNotFoundResponse();
    } catch (InvalidApiMethodException | InvalidApiQueryException | InvalidApiHeaderException | JsonException $e) {
        $response = new InternalErrorResponse("Error while getRequest: $e");
    }

    error_log("Response with " . $response->getData() . $response::class);

    //send the response
    ApiUtilities::sendResponse($response, $_ENV["API_PROD_DOMAIN"], $_ENV["API_PATH_PREFIX"]);

    exit();
}
//catch all completely unexpected exceptions
catch (Exception $e) {

    //log them
    error_log("$e");

    //send a 500 internal server error
    header_remove();
    http_response_code(500);
    exit();
}
