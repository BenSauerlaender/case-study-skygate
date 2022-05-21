<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\Objects\Responses;

use BenSauer\CaseStudySkygateApi\Exceptions\JsonException;
use BenSauer\CaseStudySkygateApi\Exceptions\ResponseExceptions\UnsupportedResponseCodeException;
use BenSauer\CaseStudySkygateApi\Exceptions\ResponseExceptions\UnsupportedResponseHeaderException;
use BenSauer\CaseStudySkygateApi\Objects\Cookies\Interfaces\CookieInterface;
use BenSauer\CaseStudySkygateApi\Objects\Responses\Interfaces\ResponseInterface;

/**
 * Base Class for API Responses
 */
abstract class BaseResponse implements ResponseInterface

{

    private int $code = 500;

    /**
     * @var array<string,CookieInterface>
     */
    private array $cookies = [];

    /**
     * @var array<string,string> in a key-value format
     */
    private array $headers = [];

    private array $data = [];

    private const SUPPORTED_CODES = [200, 201, 204, 303, 400, 401, 403, 404, 405, 406, 500];

    private const SUPPORTED_HEADER = ["Content-Type", "Last-Modified", "Location"];

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
     * @param  CookieInterface $cookie
     */
    protected function addCookie(CookieInterface $cookie): void
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
     */
    protected function setData(array $data): void
    {
        if (sizeof($data) == 0) return;

        $this->data = $data;
        $this->addHeader("Content-Type", "application/json;charset=UTF-8");
    }

    /**
     * Adds a message (msg) to the data.
     * 
     * @param  string $msg  The message to set.
     */
    protected function addMessage(string $msg)
    {
        if ($this->data === []) {
            $this->setData(["msg" => $msg]);
        } else {
            $this->data["msg"] = $msg;
        }
    }

    /**
     * Adds a error-code to the data.
     * 
     * @param  int $code  The error-code to add.
     */
    protected function addErrorCode(int $code)
    {
        if ($this->data === []) {
            $this->setData(["errorCode" => $code]);
        } else {
            $this->data["errorCode"] = $code;
        }
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

    public function getJsonString(): string
    {
        if ($this->data === []) return "";
        $str = json_encode($this->data);
        if ($str === false) throw new JsonException("The encoding of response data failed");
        return $str;
    }

    public function __toString()
    {
        $c = implode(",", array_keys($this->cookies));
        $h = implode(",", array_keys($this->headers));

        $ret = "{$this->code}: ";
        if ($c !== "") $ret = $ret . "set-cookies: $c, ";
        if ($h !== "") $ret = $ret . "headers: $h, ";
        if (!empty($this->data)) $ret = $ret . "data: {$this->getJsonString()}";
        return $ret;
    }
}
