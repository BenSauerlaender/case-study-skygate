<?php

//activate strict mode
declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\Controller\ApiController;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ServerErrorResponses\InternalErrorResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\ResourceNotFoundResponse;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiHeaderException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiMethodException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiPathException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiQueryException;
use BenSauer\CaseStudySkygateApi\Exceptions\NotSecureException;
use BenSauer\CaseStudySkygateApi\Exceptions\ShouldNeverHappenException;
use BenSauer\CaseStudySkygateApi\Objects\Request;
use BenSauer\CaseStudySkygateApi\Objects\Responses\BaseResponse;
use BenSauer\CaseStudySkygateApi\Objects\Responses\ClientErrorResponses\BadRequestResponses\BadRequestResponse;
use BenSauer\CaseStudySkygateApi\Routes;
use BenSauer\CaseStudySkygateApi\Utilities\ApiUtilities;
use BenSauer\CaseStudySkygateApi\Utilities\DbConnector;

try {
    //load composer dependencies
    require '../vendor/autoload.php';

    //load dotenv variables from '.env'
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
    $dotenv->load();

    //get the database connection
    $pdo = DbConnector::getConnection();

    //get the fully constructed apiController
    $apiController = ApiController::get($pdo, Routes::getRoutes());

    try {
        //get the request
        $request = $apiController->fetchRequest($_SERVER, getallheaders(), $_ENV["API_PATH_PREFIX"], file_get_contents('php://input'));

        //get the response
        $response = $apiController->handleRequest($request);
    } catch (InvalidArgumentException $e) {
        throw new ShouldNeverHappenException("The _SERVER variables should be always set from the apache server.", 0, $e);
    } catch (NotSecureException $e) {
        $response = new BadRequestResponse("Request was rejected, because the connection is not secured via SSL (HTTPS). Please send your request again, via HTTPS.", 311);
    } catch (InvalidApiPathException $e) {
        $response = new ResourceNotFoundResponse();
    } catch (InvalidApiMethodException | InvalidApiQueryException | InvalidApiHeaderException | JsonException $e) {
        $response = new InternalErrorResponse($e);
    }

    error_log("Response: $response");

    //send the response
    $apiController->sendResponse($response, $_ENV["API_PROD_DOMAIN"], $_ENV["API_PATH_PREFIX"]);

    //close the database connection
    $apiController = null;
    $pdo = null;
    DbConnector::closeConnection();

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
