<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\Interfaces;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiMethod;
use BenSauer\CaseStudySkygateApi\ApiComponents\Interfaces\ApiPathInterface;

/**
 * Interface for Request
 */
interface ApiRequestInterface
{
    /**
     * Gets the value of the specified parameter.
     * 
     * @return null|string|int
     */
    public function getQueryValue(string $parameter): mixed;

    /**
     * Returns the query array
     * @return array
     */
    public function getQuery(): array;

    /**
     * Gets the value of the specified header
     *
     * @param  string $key  The header's key.
     * 
     * @return string The value of the header
     */
    public function getHeader(string $key): ?string;

    /**
     * Gets the value of the specified cookie
     *
     * @param  string $key  The cookies's key.
     * 
     * @return string The value of the cookie
     */
    public function getCookie(string $key): ?string;

    /**
     * Gets the refresh token provided by the request
     * 
     * @return null|string The refresh token without any prefix or null if no token provided
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
     * Gets the http body of the request
     * 
     * @return array The Body of the request
     */
    public function getBody(): ?array;
}
