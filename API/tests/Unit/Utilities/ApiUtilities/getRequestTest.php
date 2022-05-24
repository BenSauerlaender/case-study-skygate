<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Utilities\ApiUtilities;

use BenSauer\CaseStudySkygateApi\Objects\ApiMethod;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiHeaderException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\InvalidMethodException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\InvalidPathException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\InvalidQueryException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\NotSecureException;
use BenSauer\CaseStudySkygateApi\Objects\Request;
use BenSauer\CaseStudySkygateApi\Utilities\ApiUtilities;
use JsonException;
use PHPUnit\Framework\TestCase;
use ReallySimpleJWT\Exception\JwtException;

final class getRequestTest extends TestCase
{
    /**
     * Tests if the method throws an exception if the connection is in production not secure.
     */
    public function testNoSslInProduction(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        unset($SERVER["HTTPS"]);
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "GET";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $this->expectException(NotSecureException::class);

        Request::fetch($SERVER, [], "");
    }

    /**
     * Test if the method throws an exception if the path is does not start with the prefix
     */
    public function testPathDontStartWithPrefix(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = "test";
        $SERVER["REQUEST_URI"] = "/path/to/x";
        $SERVER["REQUEST_METHOD"] = "GET";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $this->expectException(InvalidPathException::class);

        Request::fetch($SERVER, [], "pre");
    }

    /**
     * Test if the method throws an exception if the path is not valid
     */
    public function testPathIsInvalid(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/x.txt";
        $SERVER["REQUEST_METHOD"] = "GET";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $this->expectException(InvalidPathException::class);

        Request::fetch($SERVER, [], "/path/to");
    }

    /**
     * Test if the method throws an exception if the method is not valid
     */
    public function testMethodIsInvalid(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "SEARCH";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $this->expectException(InvalidMethodException::class);

        Request::fetch($SERVER, [], "/path/to");
    }

    /**
     * Test if the method throws an exception if the query is not valid
     */
    public function testQueryIsInvalid(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "GET";
        $SERVER["QUERY_STRING"] = "123";

        $this->expectException(InvalidQueryException::class);

        Request::fetch($SERVER, [], "/path/to");
    }

    /**
     * Test if the method throws an exception if a header is not valid
     */
    public function testHeaderIsInvalid(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "GET";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $this->expectException(InvalidApiHeaderException::class);

        Request::fetch($SERVER, [1 => "test"], "/path/to");
    }

    /**
     * Test if the method throws an exception if a cookie is not valid
     */
    public function testCookieIsInvalid(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "GET";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $this->expectException(InvalidApiHeaderException::class);

        Request::fetch($SERVER, ["t1" => "test", "Cookie" => "1 2 3 "], "/path/to");
    }

    /**
     * Test if the method throws an exception if the body cant be decoded.
     */
    public function testBodyIsInvalid(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "POST";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $this->expectException(JsonException::class);

        Request::fetch($SERVER, [], "/path/to", "/////");
    }

    /**
     * Test if the body is null if the Method is not PUT or POST.
     */
    public function testBodyOnGet(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "GET";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $req = Request::fetch($SERVER, [], "/path/to", '{ "test" : 123}');
        $this->assertNull($req->getBody());
    }

    /**
     * Test if the body is null if the body string is empty.
     */
    public function testBodyIsEmpty(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "POST";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $req = Request::fetch($SERVER, [], "/path/to", "");
        $this->assertNull($req->getBody());
    }

    /**
     * Test if the method works as expected
     */
    public function testSuccessful(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt?123";
        $SERVER["REQUEST_METHOD"] = "POST";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $req = Request::fetch($SERVER, ["t1" => "test", "Cookie" => "c=cookie123"], "/path/to", '{ "test" : 123}');

        $this->assertEquals("/txt", $req->getPath());
        $this->assertEquals(ApiMethod::POST, $req->getMethod());
        $this->assertEquals(3, $req->getQueryValue("search"));
        $this->assertEquals("test", $req->getQueryValue("p"));
        $this->assertEquals("test", $req->getHeader("t1"));
        $this->assertEquals("cookie123", $req->getCookie("c"));
        $this->assertEquals(123, $req->getBody()["test"]);
    }
}
