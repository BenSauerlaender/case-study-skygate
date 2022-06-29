<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Objects\Interfaces;

use Objects\ApiMethod;
use Objects\Interfaces\ApiPathInterface;

/**
 * Object to represent an user request, that comes to the api
 */
interface RequestInterface
{
    /**
     * Gets the value of the specified query-parameter.
     * 
     * If the query parameter is set but has no value, the parameter itself will be returned.
     * 
     * @param string $parameter     The query-parameter.
     * @return null|string|int      The query-parameters value.
     */
    public function getQueryValue(string $parameter): mixed;

    /**
     * Returns the all query parameter-value pairs
     *
     * For query parameter, that are set but have no values, the parameter itself will be also the value.
     * 
     * @return array<string,string|int> A list of parameter-value pairs
     */
    public function getQuery(): array;

    /**
     * Gets the value of the specified header
     *
     * @param string $key   The header's key.
     * @return string       The header's value.
     */
    public function getHeader(string $key): ?string;

    /**
     * Gets the value of the specified cookie
     *
     * @param  string $key  The cookies's key.
     * @return string       The value of the cookie.
     */
    public function getCookie(string $key): ?string;

    /**
     * Gets the access token if there is one.
     * 
     * @return null|string The access token without any prefix or null if no token provided
     */
    public function getAccessToken(): ?string;

    /**
     * Gets the requested Path
     * 
     * @return ApiPathInterface The requested api path
     */
    public function getPath(): ApiPathInterface;

    /**
     * Gets the requested Method
     * 
     * @return ApiMethod The requestedMethod
     */
    public function getMethod(): ApiMethod;

    /**
     * Gets the http body of the request if there is one
     * 
     * @return array The Body of the request or null if there is no body.
     */
    public function getBody(): ?array;
}
