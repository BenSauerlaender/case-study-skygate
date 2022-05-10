<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\ApiComponents\ApiRequests;

use BenSauer\CaseStudySkygateApi\ApiComponents\ApiMethod;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\Interfaces\ApiRequestInterface;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\Request;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiCookieException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiHeaderException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiMethodException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiPathException;
use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiQueryException;
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
        $req = new Request("/test/jo", "GET", "t1=123&t2=test2", ["header1" => "h1"], ["test" => 12]);
        $this->assertTrue(is_a($req, ApiRequestInterface::class));
    }

    /**
     * Tests if the Constructor throws InvalidApiPathException if the path is not valid.
     */
    public function testConstructorWithInvalidApiPath(): void
    {
        $this->expectException(InvalidApiPathException::class);

        new Request("test-jo", "GET");
    }

    /**
     * Tests if the Constructor throws InvalidApiMethodException if the method is not valid.
     */
    public function testConstructorWithInvalidApiMethod(): void
    {
        $this->expectException(InvalidApiMethodException::class);

        new Request("test/jo", "Quatsch");
    }

    /**
     * Tests if the Constructor throws InvalidApiQueryException if the query is not valid.
     */
    public function testConstructorWithInvalidApiQuery(): void
    {
        $this->expectException(InvalidApiQueryException::class);

        new Request("test/jo", "GET", "quatsch");
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
     * Tests if the Constructor throws InvalidApiCookieException if the cookie header is not valid.
     */
    public function testConstructorWithInvalidApiCookie1(): void
    {
        $this->expectException(InvalidApiCookieException::class);

        new Request("test/jo", "GET", "", ["cookie" => "123"]);
    }

    /**
     * Tests if the Constructor throws InvalidApiCookieException if the cookie header is not valid.
     */
    public function testConstructorWithInvalidApiCookie2(): void
    {
        $this->expectException(InvalidApiCookieException::class);

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
            ["t1=test1", "t1", "test1"],
            ["t1=test1&t2=123", "t1", "test1"],
            ["t2=123&t1=test1", "t1", "test1"],
            ["t2=123&T1=test1", "t1", "test1"],
            ["t2=123&T1=test1", "T1", "test1"],
            ["t2=123&t1=test1", "T1", "test1"],
            ["t2=123&t1=TEST1", "T1", "test1"],
            ["t2=123&t1=test1", "t2", 123],
            ["t2=123&t1=test1", "t3", null]
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
