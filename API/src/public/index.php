<?php

//activate strict mode
declare(strict_types=1);

try{
    //load composer dependencies
    require './../../vendor/autoload.php';

    //load dotenv variables from '.env'
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../", ".env");
    $dotenv->load();

    $path = $_SERVER["REQUEST_URI"];
    $method = $_SERVER["REQUEST_METHOD"];
    $params = $_SERVER["QUERY_STRING"];
    $headers = //acceptence

    if(isNot /API/v1/)
    $response = new NotFoundResponse
    else if(no https)
    $response = new NotsecureResponse
    else {
        $handler = handlerfactory($path)

        $request = new Request(....)
        $response = $handler->handle($request);

    }

    $response->send();
    exit();


}catch(Exception $e){
    error_log($e);
    header("Internal Error");
    exit();
}
