<?php

/**
 * The APIs entrypoint. 
 * 
 * It:
 * - fetches the request
 * - connects to the Database
 * - initialize all controllers
 * - let the ApiController handle the request and send the response.
 * 
 * @package root
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */


//activate strict mode
declare(strict_types=1);

use Controller\ApiController;
use Objects\Responses\ClientErrorResponses\ResourceNotFoundResponse;
use Exceptions\InvalidRequestExceptions\InvalidPathException;
use Exceptions\InvalidRequestExceptions\NotSecureException;
use Exceptions\InvalidRequestExceptions\InvalidRequestException;
use Exceptions\ShouldNeverHappenException;
use Objects\Responses\ClientErrorResponses\BadRequestResponses\BadRequestResponse;
use Utilities\DbConnector;

try {
    //load composer dependencies
    require '../vendor/autoload.php';

    //load dotenv variables from '.env'
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
    $dotenv->load();

    //get the database connection
    $pdo = DbConnector::getConnection();

    //get the fully constructed apiController
    $apiController = ApiController::get($pdo, Routes::getRoutes(), Permissions::getPermissions());

    try {
        //get the request
        $request = $apiController->fetchRequest($_SERVER, getallheaders(), $_ENV["API_PATH_PREFIX"], file_get_contents('php://input'));

        //get the response
        $response = $apiController->handleRequest($request);
    } catch (InvalidArgumentException $e) {
        throw new ShouldNeverHappenException("The _SERVER variables should be always set from the apache server.", $e);
    } catch (NotSecureException $e) {
        $response = new BadRequestResponse("Request was rejected, because the connection is not secured via SSL (HTTPS). Please send your request again, via HTTPS.", 311);
    } catch (InvalidPathException $e) {
        $response = new ResourceNotFoundResponse();
    } catch (InvalidRequestException $e) { //Invalid Method, Query, Cookie 
        $response = new BadRequestResponse("The Request can't be parsed properly: " . $e->getMessage(), 401);
    }

    error_log("Response: $response");

    //send the response
    $apiController->sendResponse($response, explode(":", $_ENV["API_PROD_DOMAIN"])[0], $_ENV["API_PATH_PREFIX"]);

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
