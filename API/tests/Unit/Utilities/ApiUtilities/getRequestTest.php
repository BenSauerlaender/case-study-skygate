<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Utilities\ApiUtilities;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiMethod;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiHeaderException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiMethodException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiPathException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiQueryException;
use BenSauer\CaseStudySkygateApi\Exceptions\NotSecureException;
use BenSauer\CaseStudySkygateApi\Utilities\ApiUtilities;
use PHPUnit\Framework\TestCase;

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

        ApiUtilities::getRequest($SERVER, [], "");
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

        $this->expectException(InvalidApiPathException::class);

        ApiUtilities::getRequest($SERVER, [], "pre");
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

        $this->expectException(InvalidApiPathException::class);

        ApiUtilities::getRequest($SERVER, [], "/path/to");
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

        $this->expectException(InvalidApiMethodException::class);

        ApiUtilities::getRequest($SERVER, [], "/path/to");
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
        $SERVER["QUERY_STRING"] = "SEARCH";

        $this->expectException(InvalidApiQueryException::class);

        ApiUtilities::getRequest($SERVER, [], "/path/to");
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

        ApiUtilities::getRequest($SERVER, [1 => "test"], "/path/to");
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

        ApiUtilities::getRequest($SERVER, ["t1" => "test", "Cookie" => "1 2 3 "], "/path/to");
    }

    /**
     * Test if the method works as expected
     */
    public function testSuccessful(): void
    {
        $_ENV["ENVIRONMENT"] = "PRODUCTION";
        $SERVER = [];
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/txt";
        $SERVER["REQUEST_METHOD"] = "GET";
        $SERVER["QUERY_STRING"] = "search=3&p=test";

        $req = ApiUtilities::getRequest($SERVER, ["t1" => "test", "Cookie" => "c=cookie123"], "/path/to");

        $this->assertEquals("/txt", $req->getPath());
        $this->assertEquals(ApiMethod::GET, $req->getMethod());
        $this->assertEquals(3, $req->getQueryValue("search"));
        $this->assertEquals("test", $req->getQueryValue("p"));
        $this->assertEquals("test", $req->getHeader("t1"));
        $this->assertEquals("cookie123", $req->getCookie("c"));
    }
}
