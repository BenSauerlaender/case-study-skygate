<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiMethod;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiPath;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\Interfaces\ApiRequestInterface;
use BenSauer\CaseStudySkygateApi\ApiComponents\Interfaces\ApiPathInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiCookieException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiHeaderException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiQueryException;
use phpDocumentor\Reflection\PseudoTypes\NonEmptyString;

/**
 * Class that represent an Request to the API
 */
class Request implements ApiRequestInterface
{
    private ApiPathInterface $path;

    private ApiMethod $method;

    /** @var array<string,string> */
    private array $headers;

    /** @var array<string,string> */
    private array $cookies;

    /** @var array<string,string|int> */
    private array $query;

    private ?array $body;

    /**
     * Constructs a request
     *
     * @param  string $path                     The requested path as string without a prefix (like /api/v1)
     * @param  string $method                   The request method. E.g. GET
     * @param  string $query                    The query string from the request
     * @param  array<string,string>  $headers   The headers provided by the request.
     * 
     * @throws InvalidApiPathException      if the path string can not parsed into an ApiPath.
     * @throws InvalidApiMethodException    if the method string can not parsed into an ApiMethod.
     * @throws InvalidApiQueryException     if the query string can not be parsed into an valid array.
     * @throws InvalidApiHeaderException    if a header can not be parsed into an valid array.
     * @throws InvalidApiCookieException    if a cookie can not be parsed into an valid array.
     */
    public function __construct(string $path, string $method, string $query = "", array $headers = [], ?array $body = null)
    {

        $this->path = new ApiPath($path);

        $this->method = ApiMethod::fromString($method);

        $this->query = $this->parseQuery($query);

        $cookieAndHeader = $this->parseHeaders($headers);

        $this->headers = $cookieAndHeader["headers"];
        $this->cookies = $cookieAndHeader["cookies"];

        $this->body = $body;
    }

    /**
     * Parses a query string in a query array
     *
     * @param  string $query    The Raw query string.
     * 
     * @throws InvalidApiQueryException if the query string can not be parsed into an valid array.
     */
    private function parseQuery(string $query): array
    {
        $ret = [];

        if ($query != "") {
            //for each query pair //also lowercase and remove spaces
            foreach (explode("&", str_replace(" ", "", $query)) as $p) {
                //separate parameter name from value
                $pair = explode("=", $p);

                $pair[0] = strtolower($pair[0]);

                if (preg_match("/^[a-z]+$/", $pair[0]) !== 1) throw new InvalidApiQueryException("The query string part: '$p' is not valid");

                //no value: set key also as value
                if (sizeof($pair) === 1) {
                    $pair[1] = $pair[0];
                }
                if (sizeof($pair) !== 2) throw new InvalidApiQueryException("The query string part: '$p' is not valid");

                //if value is an int get as int otherwise get the string
                $val = filter_var($pair[1], FILTER_VALIDATE_INT);
                if ($val === false) {
                    $val = str_replace("+", " ", $pair[1]);
                }
                //save the query pair
                $ret[$pair[0]] = $val;
            }
        }
        return $ret;
    }

    /**
     * Parses a headers array in the private headers- and cookies- arrays
     *
     * @param  array<string,string> $headers    The Raw query string.
     * 
     * @throws InvalidApiHeaderException if a header can not be parsed into an valid array.
     * @throws InvalidApiCookieException if a cookie can not be parsed into an valid array.
     */
    private function parseHeaders(array $headers): array
    {
        $retHeaders = [];
        $retCookies = [];

        //for each header
        foreach ($headers as $key => $value) {
            if (!is_string($key)) throw new InvalidApiHeaderException("The key: '$key' is not a string.");
            if (!is_string($value)) throw new InvalidApiHeaderException("The value: '$value' is not a string.");

            //key to lowercase
            $key = strtolower($key);

            //if key is cookie save in cookies not in headers
            if ($key == "cookie") {
                foreach (explode("; ", $value) as $cookie) {
                    //separate key and value
                    $pair = explode("=", $cookie);

                    if (sizeof($pair) !== 2) throw new InvalidApiCookieException("The cookie: '$cookie' is not valid");

                    //save the cookie
                    $retCookies[strtolower($pair[0])] = $pair[1];
                }
            } else {
                //save the header
                $retHeaders[$key] = $value;
            }
        }
        return ["cookies" => $retCookies, "headers" => $retHeaders];
    }

    public function getQueryValue(string $parameter): mixed
    {
        $parameter = strtolower($parameter);
        if (array_key_exists($parameter, $this->query)) {
            return $this->query[$parameter];
        } else {
            return null;
        }
    }

    public function getHeader(string $key): ?string
    {
        $key = strtolower($key);
        if (array_key_exists($key, $this->headers)) {
            return $this->headers[$key];
        } else {
            return null;
        };
    }

    public function getCookie(string $key): ?string
    {
        $key = strtolower($key);
        if (array_key_exists($key, $this->cookies)) {
            return $this->cookies[$key];
        } else {
            return null;
        };
    }

    public function getAccessToken(): ?string
    {
        //get authorization header 
        $token = $this->getHeader("Authorization");

        //return the token if it has the correct format: 'Bearer <token>'
        if (is_string($token)) {
            $exploded = explode(" ", $token);
            if (sizeof($exploded) === 2) {
                if ($exploded[0] === "Bearer") {
                    return $exploded[1];
                }
            }
        }

        return null;
    }

    public function getPath(): ApiPathInterface
    {
        return $this->path;
    }

    public function getMethod(): ApiMethod
    {
        return $this->method;
    }

    public function getBody(): ?array
    {
        return $this->body;
    }
}
