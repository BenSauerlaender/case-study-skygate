<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Objects;

use BenSauer\CaseStudySkygateApi\Objects\ApiMethod;
use BenSauer\CaseStudySkygateApi\Objects\Interfaces\RequestInterface;
use BenSauer\CaseStudySkygateApi\Objects\Request;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\InvalidCookieException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiHeaderException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\InvalidMethodException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\InvalidPathException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestExceptions\InvalidQueryException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the BaseResponse abstract class
 */
final class ApiRequestTest extends TestCase
{
    /**
     * Tests if the Constructor works as expected.
     */
    public function testConstructor(): void
    {
        $req = new Request("/test/jo", "GET", "test=123&alpha=Test2&abc", ["header1" => "h1"], ["test" => 12]);
        $this->assertTrue(is_a($req, RequestInterface::class));
    }

    /**
     * Tests if the Constructor throws InvalidPathException if the path is not valid.
     */
    public function testConstructorWithInvalidApiPath(): void
    {
        $this->expectException(InvalidPathException::class);

        new Request("test-jo", "GET");
    }

    /**
     * Tests if the Constructor throws InvalidMethodException if the method is not valid.
     */
    public function testConstructorWithInvalidApiMethod(): void
    {
        $this->expectException(InvalidMethodException::class);

        new Request("test/jo", "Quatsch");
    }

    /**
     * Tests if the Constructor throws InvalidQueryException if the query is not valid.
     */
    public function testConstructorWithInvalidApiQuery(): void
    {
        $this->expectException(InvalidQueryException::class);

        new Request("test/jo", "GET", "123");
    }

    /**
     * Tests if the Constructor throws InvalidApiHeaderException if the header is not valid.
     */
    public function testConstructorWithInvalidHeader1(): void
    {
        $this->expectException(InvalidApiHeaderException::class);

        new Request("test/jo", "GET", "", ["h1"]);
    }

    /**
     * Tests if the Constructor throws InvalidHeaderException if the header is not valid.
     */
    public function testConstructorWithInvalidHeader2(): void
    {
        $this->expectException(InvalidApiHeaderException::class);

        new Request("test/jo", "GET", "", ["h1" => 123]);
    }

    /**
     * Tests if the Constructor throws InvalidCookieException if the cookie header is not valid.
     */
    public function testConstructorWithInvalidApiCookie1(): void
    {
        $this->expectException(InvalidCookieException::class);

        new Request("test/jo", "GET", "", ["cookie" => "123"]);
    }

    /**
     * Tests if the Constructor throws InvalidCookieException if the cookie header is not valid.
     */
    public function testConstructorWithInvalidApiCookie2(): void
    {
        $this->expectException(InvalidCookieException::class);

        new Request("test/jo", "GET", "", ["cookie" => "123=fds=2"]);
    }

    /**
     * Tests if request->getQueryValue responses the correct query value.
     * 
     * @dataProvider queryProvider
     */
    public function testGetQueryValue(string $query, string $param, mixed $value): void
    {
        $req = new Request("test/jo", "GET", $query);

        $this->assertEquals($value, $req->getQueryValue($param));
    }

    public function queryProvider(): array
    {
        return [
            ["test=test1", "test", "test1"],
            ["test=test1&alpha=123", "test", "test1"],
            ["alpha=123&test=test1", "test", "test1"],
            ["alpha=123&test=test1", "test", "test1"],
            ["alpha=123&test=teSt1", "test", "teSt1"],
            ["alpha=123&test=test1", "test", "test1"],
            ["alpha=123&test=TEST1", "test", "TEST1"],
            ["alpha=123&test=test1", "alpha", 123],
            ["alpha&test=test1", "alpha", "alpha"],
            ["alpha=eins+zwei+drei&test=test1", "alpha", "eins zwei drei"],
            ["alpha=123&test=test1", "nice", null]
        ];
    }

    /**
     * Tests if request->getHeader responses the correct header value.
     * 
     * @dataProvider headerProvider
     */
    public function testGetHeader(array $headers, string $header, ?string $value): void
    {
        $req = new Request("test/jo", "GET", "", $headers);

        $this->assertEquals($value, $req->getHeader($header));
    }

    public function headerProvider(): array
    {
        return [
            [["h1" => "test1"], "h1", "test1"],
            [["h1" => "test1", "h2" => "test2"], "h1", "test1"],
            [["h1" => "test1", "h2" => "test2"], "h1", "test1"],
            [["h1" => "test1", "h2" => "test2"], "h2", "test2"],
            [["h1" => "test1", "h2" => "t es t2"], "h2", "t es t2"],
            [["H1" => "tEST1", "h2" => "test2"], "h1", "tEST1"],
            [["h1" => "test1", "h2" => "test2"], "H2", "test2"],
            [["h1" => "test1", "h2" => "test2"], "h3", null],
            [["Cookie" => "test1=2", "h2" => "test2"], "Cookie", null]
        ];
    }

    /**
     * Tests if request->getCookie responses the correct cookie value.
     * 
     * @dataProvider cookieProvider
     */
    public function testGetCookie(string $cookies, string $cookie, ?string $value): void
    {
        $req = new Request("test/jo", "GET",  "", ["Cookie" => $cookies]);

        $this->assertEquals($value, $req->getCookie($cookie));
    }

    public function cookieProvider(): array
    {
        return [
            ["c1=test1", "c1", "test1"],
            ["C1=test1", "c1", "test1"],
            ["C1=tEST1", "C1", "tEST1"],
            ["c1=test1", "C1", "test1"],
            ["c1=test1; c1=test2", "c1", "test2"],
            ["c1=test1; c2=test2", "c2", "test2"],
            ["c1=test1; c2=test2", "c3", null],
        ];
    }

    /**
     * Tests if request->getAccessToken responses the correct token.
     * 
     * @dataProvider tokenProvider
     */
    public function testGetAccessToken(array $header, mixed $token): void
    {
        $req = new Request("test/jo", "GET",  "", $header);

        $this->assertEquals($token, $req->getAccessToken());
    }

    public function tokenProvider(): array
    {
        return [
            [["Auth" => "Bearer token"], null],
            [["Authorization" => "token"], null],
            [["Authorization" => "Bearer token"], "token"],
            [["Authorization" => "Bearer tok en"], null],
            [["Authorization" => "Bearer TOken"], "TOken"]
        ];
    }

    /**
     * Tests if request->getPath responses the correct path.
     */
    public function testGetPath(): void
    {
        $req = new Request("test/jo/", "GET");

        $this->assertEquals("/test/jo", strval($req->getPath()));
    }

    /**
     * Tests if request->getMethod responses the correct method.
     */
    public function testGetMethod(): void
    {
        $req = new Request("test/jo/", "GET");

        $this->assertEquals(ApiMethod::GET, $req->getMethod());
    }

    /**
     * Tests if request->getMethod responses null if the request has no body.
     */
    public function testGetEmptyBody(): void
    {
        $req = new Request("test/jo/", "GET");

        $this->assertNull($req->getBody());
    }

    /**
     * Tests if request->getMethod responses the correct body.
     */
    public function testGetBody(): void
    {
        $req = new Request("test/jo/", "GET",  "", [], ["key" => 123]);

        $this->assertEquals(["key" => 123], $req->getBody());
    }
}
