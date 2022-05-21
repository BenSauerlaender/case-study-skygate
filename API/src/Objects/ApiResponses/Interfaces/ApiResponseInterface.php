<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\ApiResponses\Interfaces;

/**
 * Interface for Response
 */
interface ApiResponseInterface
{
    /**
     * Returns the response code
     * 
     * @return int The response code
     */
    public function getCode(): int;

    /**
     * Returns an array of cookies to be set.
     * 
     * @return array<ResponseCookieInterface> The array of Cookies.
     */
    public function getCookies(): array;

    /**
     * Returns the headers to send.
     * 
     * @return array<string,string> An Array of key-value-pairs for the header.
     */
    public function getHeaders(): array;

    /**
     * Returns the data in json format to send in the response body
     * 
     * @return string The json encoded data.
     * @throws JsonException if the encoding fails.
     */
    public function getJsonString(): string;
}
