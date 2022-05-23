<?php

//activate strict mode
declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\Objects\Responses\ServerErrorResponses\InternalErrorResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\ResourceNotFoundResponse;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiHeaderException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiMethodException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiPathException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiQueryException;
use BenSauer\CaseStudySkygateApi\Exceptions\NotSecureException;
use BenSauer\CaseStudySkygateApi\Objects\Responses\BaseResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\BadRequestResponses\BadRequestResponse;
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
        $response = new BadRequestResponse("Request was rejected, because the connection is not secured via SSL (HTTPS). Please send your request again, via HTTPS.", 311);
    } catch (InvalidApiPathException $e) {
        $response = new ResourceNotFoundResponse();
    } catch (InvalidApiMethodException | InvalidApiQueryException | InvalidApiHeaderException | JsonException $e) {
        $response = new InternalErrorResponse($e);
    }

    error_log("Response: $response");

    //send the response
    BaseResponse::send($response, $_ENV["API_PROD_DOMAIN"], $_ENV["API_PATH_PREFIX"]);

    exit();
}
//catch all completely unexpected exceptions
catch (Throwable $e) {

    //log them
    error_log("$e");

    //send a 500 internal server error
    header_remove();
    http_response_code(500);
    exit();
}
