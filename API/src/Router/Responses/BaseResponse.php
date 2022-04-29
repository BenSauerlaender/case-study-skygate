<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router\Responses;

use BenSauer\CaseStudySkygateApi\Router\Requests\ResponseInterface;

/**
 * Base Class for API Responses
 */
abstract class BaseResponse implements ResponseInterface
{
    /**
     * Sets the Response Code
     *
     * @param  int  $code
     * @throws UnsupportedResponseCodeException if the code is not supported.
     */
    protected function setCode(int $code): void
    {
    }

    /**
     * Adds a cookie to be send.
     *
     * @param  ResponseCookieInterface $cookie
     */
    protected function addCookie(ResponseCookieInterface $cookie): void
    {
    }

    /**
     * Adds a header to be send.
     *
     * @param  string $name     The Headers name.
     * @param  string $value    The headers value.
     * 
     * @throws UnsupportedResponseHeaderException
     */
    protected function addHeader(string $name, string $value): void
    {
    }

    /**
     * Sets data to be send in body
     * 
     * @param array $data
     */
    protected function setData(array $data): void
    {
    }

    public function getCode(): int
    {
        return 0;
    }

    public function getCookies(): array
    {
        return [];
    }

    public function getHeaders(): array
    {
        return [];
    }

    public function getJson(): string
    {
        return "";
    }
}
