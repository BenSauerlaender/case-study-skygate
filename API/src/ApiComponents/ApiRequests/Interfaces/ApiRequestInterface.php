<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router\Interfaces;

/**
 * Interface for Request
 */
interface ApiRequestInterface
{
    /**
     * Gets the Query in a key-value format.
     * 
     * @return array<string,string>
     */
    public function getQuery(): array;

    /**
     * Gets the value of the specified header
     *
     * @param  string $key  The header's key.
     * 
     * @return string The value of the header
     */
    public function getHeader(string $key): string;

    /**
     * Gets the accessToken provided by the request
     * 
     * @return string The access token without any prefix
     * 
     * @throws NoAccessTokenProvided if the request dont have an access token.
     */

    public function getRefreshToken(): string;

    /**
     * Gets the refresh token provided by the request
     * 
     * @return null|string The refresh token without any prefix or null if no token provided
     */
    public function getAccessToken(): ?string;
}
