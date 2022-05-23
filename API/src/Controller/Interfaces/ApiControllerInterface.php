<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Controller\Interfaces;

use BenSauer\CaseStudySkygateApi\Objects\Interfaces\RequestInterface;
use BenSauer\CaseStudySkygateApi\Objects\Responses\Interfaces\ResponseInterface;

/**
 * Main Controller for the whole api
 */
interface ApiControllerInterface
{
    /**
     * Constructs a request straight from the server-set variables
     *
     * @param  array    $server      The $_SERVER array.
     * @param  array    $headers     The response array of getallheaders().
     * @param  string   $pathPrefix  The prefix in front of an api path e.g. /api/v1/.
     *
     * @throws InvalidApiHeaderException    if the server-array is not complete.
     * @throws NotSecureException           if the request comes not from https in prod.
     * @throws InvalidApiPathException      if the path string can not parsed into an ApiPath.
     * @throws InvalidApiMethodException    if the method string can not parsed into an ApiMethod.
     * @throws InvalidApiQueryException     if the query string can not be parsed into an valid array.
     * @throws InvalidApiHeaderException    if a header can not be parsed into an valid array.
     */
    public function fetchRequest(array $server, array $headers, string $pathPrefix, string $bodyJSON = ""): RequestInterface;

    /**
     * Takes an request, process it and returns a response.
     *
     * @param  RequestInterface  $request
     * @return ResponseInterface
     */
    public function handleRequest(RequestInterface $request): ResponseInterface;

    /**
     * Send a response to the client
     *
     * @param  ResponseInterface    $response       The response to be send
     * @param  string               $domain         The Servers Domain.
     * @param  string               $pathPrefix     The APIs path prefix.
     */
    public function sendResponse(ResponseInterface $response, string $domain, string $pathPrefix): void;
}
