<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace Controller\Interfaces;

use Objects\Interfaces\RequestInterface;
use Objects\Responses\Interfaces\ResponseInterface;

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
     * @throws InvalidArgumentException     if the server-array is not complete.
     * @throws InvalidRequestException             if the input can not be parsed in a valid request
     *      (InvalidPathException | InvalidMethodException | InvalidQueryException | InvalidCookieException | NotSecureException)
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
