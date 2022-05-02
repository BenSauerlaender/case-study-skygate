<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Router\Responses;

use BenSauer\CaseStudySkygateApi\Exceptions\JsonEncodingException;
use BenSauer\CaseStudySkygateApi\Exceptions\ResponseExceptions\UnsupportedResponseCodeException;
use BenSauer\CaseStudySkygateApi\Exceptions\ResponseExceptions\UnsupportedResponseHeaderException;
use BenSauer\CaseStudySkygateApi\Router\Responses\Interfaces\ResponseCookieInterface;
use BenSauer\CaseStudySkygateApi\Router\Responses\Interfaces\ResponseInterface;

/**
 * Base Class for API Responses
 */
abstract class BaseResponse implements ResponseInterface
{

    private int $code = 501;

    /**
     * @var array<string,ResponseCookieInterface>
     */
    private array $cookies = [];

    /**
     * @var array<string,string> in a key-value format
     */
    private array $headers = [];

    private string $data = "";

    private const SUPPORTED_CODES = [200, 201, 204, 400, 401, 403, 404, 405, 406, 501];

    private const SUPPORTED_HEADER = ["Content-Type", "Last-Modified"];

    /**
     * Sets the Response Code
     *
     * @param  int  $code
     * @throws UnsupportedResponseCodeException if the code is not supported.
     */
    protected function setCode(int $code): void
    {
        if (!in_array($code, self::SUPPORTED_CODES)) throw new UnsupportedResponseCodeException("The Response code: $code is not supported");
        $this->code = $code;
    }

    /**
     * Adds a cookie to be send.
     *
     * @param  ResponseCookieInterface $cookie
     */
    protected function addCookie(ResponseCookieInterface $cookie): void
    {
        $this->cookies[strtolower($cookie->getName())] = $cookie;
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
        //search case insensitive for one of the supported headers
        $saveName = array_values(array_filter(self::SUPPORTED_HEADER, function ($v) use ($name) {
            return !strcasecmp($v, $name);
        }));

        //check if supported header was found
        if (sizeof($saveName) !== 1) throw new UnsupportedResponseHeaderException("The Response Header $name is not supported");

        //save the new header
        $this->headers[$saveName[0]] = $value;
    }

    /**
     * Sets data to be send in body
     * 
     * @param array $data
     * @throws JsonEncodingException if the encoding fails.
     */
    protected function setData(array $data): void
    {
        if (sizeof($data) == 0) $str = "";

        $str = json_encode($data);
        if ($str === false) throw new JsonEncodingException("The encoding of response data failed");

        $this->data = $str;

        $this->addHeader("Content-Type", "application/json;charset=UTF-8");
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getCookies(): array
    {
        return array_values($this->cookies);
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getData(): string
    {
        return $this->data;
    }
}
