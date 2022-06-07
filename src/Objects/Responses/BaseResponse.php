<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace Objects\Responses;

use Exceptions\InvalidResponseExceptions\UnsupportedResponseCodeException;
use Exceptions\InvalidResponseExceptions\UnsupportedResponseHeaderException;
use Objects\Cookies\Interfaces\CookieInterface;
use Objects\Responses\Interfaces\ResponseInterface;

/**
 * abstract base Class for API Responses
 */
abstract class BaseResponse implements ResponseInterface
{
    /** The http response code */
    private int $code = 500;

    /**
     * Cookies to set in name-cookie pair list
     * 
     * @var array<string,CookieInterface>
     */
    private array $cookies = [];

    /**
     * Headers in a key-value format
     * 
     * @var array<string,string> 
     */
    private array $headers = [];

    /** response body array that will send as json */
    private array $body = [];

    /** Http response codes, that are supported by the api */
    private const SUPPORTED_CODES = [200, 201, 204, 303, 400, 401, 403, 404, 405, 406, 500];

    /** Http response headers, that are supported by the api */
    private const SUPPORTED_HEADERS = ["content-type", "last-modified", "location", "access-control-allow-origin", "access-control-allow-methods", "access-control-allow-headers"];

    /**
     * Sets the Http response code
     *
     * @param  int  $code                           The code to set.
     * @throws UnsupportedResponseCodeException     if the code is not supported.
     */
    protected function setCode(int $code): void
    {
        //check if its supported
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
        //adds the cookie with lowercase name as key.
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
        //convert the name to lower case, so its not case sensitive
        $name = strtolower($name);

        //check if header is supported
        if (!in_array($name, self::SUPPORTED_HEADERS)) throw new UnsupportedResponseHeaderException("The Response Header $name is not supported");

        //save the new header
        $this->headers[$name] = $value;
    }

    /**
     * Sets the response body
     * 
     * ATTENTION: This will override the previous set Body (inclusive msg and errorCode).
     * 
     * @param array $body   The body, that will be send as json.
     */
    protected function setBody(array $body): void
    {
        //if empty: do nothing
        if (sizeof($body) == 0) return;

        $this->body = $body;

        //add the content-type header
        $this->addHeader("content-type", "application/json;charset=UTF-8");
    }

    /**
     * Adds a message (msg) to the body.
     * 
     * @param  string $msg  The message to set.
     */
    protected function addMessage(string $msg)
    {
        //if there is no body jet: create one
        if ($this->body === []) {
            $this->setBody(["msg" => $msg]);
        } else { //else: just add the msg key
            $this->body["msg"] = $msg;
        }
    }

    /**
     * Adds an error-code to the body.
     * 
     * @param  int $code  The error-code to add.
     */
    protected function addErrorCode(int $code)
    {
        //if there is no body jet: create one
        if ($this->body === []) {
            $this->setBody(["errorCode" => $code]);
        } else { //else: just add the errorCode key
            $this->body["errorCode"] = $code;
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

    public function getJsonBody(): string
    {
        //if there is no body
        if ($this->body === []) return "";

        //encode the body
        $json = json_encode($this->body, JSON_THROW_ON_ERROR);

        return $json;
    }

    public function __toString()
    {
        //implode the cookies and headers to comma separated strings
        $c = implode(",", array_keys($this->cookies));
        $h = implode(",", array_keys($this->headers));

        //construct the string
        $ret = "";

        //add the response code
        $ret = $ret . "{$this->code}: ";

        //add the cookies if there is at least one 
        if ($c !== "") $ret = $ret . "set-cookies: $c, ";

        //add the headers if there is at least one 
        if ($h !== "") $ret = $ret . "headers: $h, ";

        //add the body if there is one
        if (!empty($this->body)) $ret = $ret . "body: {$this->getJsonBody()}";

        return $ret;
    }
}
