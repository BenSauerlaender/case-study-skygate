<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Router\Response;

use BenSauer\CaseStudySkygateApi\Exceptions\ResponseExceptions\UnsupportedResponseCodeException;
use BenSauer\CaseStudySkygateApi\Exceptions\ResponseExceptions\UnsupportedResponseHeaderException;
use BenSauer\CaseStudySkygateApi\Router\Responses\BaseResponse;
use BenSauer\CaseStudySkygateApi\Router\Responses\ResponseCookieInterface;
use PHPUnit\Framework\TestCase;

class mockResponse extends BaseResponse
{
    public function setCode(int $code): void
    {
        parent::setCode($code);
    }

    public function addCookie(ResponseCookieInterface $cookie): void
    {
        parent::addCookie($cookie);
    }

    public function addHeader(string $name, string $value): void
    {
        parent::addHeader($name, $value);
    }

    public function setData(array $data): void
    {
        parent::setData($data);
    }
}
/**
 * Tests for the BaseResponse abstract class
 */
final class BaseResponseTest extends TestCase
{
    /**
     * Tests if the Response returns the correct default fields.
     */
    public function testDefault(): void
    {
        $response = new mockResponse();

        $this->assertEquals(501, $response->getCode());
        $this->assertEmpty($response->getCookies());
        $this->assertEmpty($response->getHeaders());
        $this->assertEmpty($response->getJson());
    }

    /**
     * Tests if the Response throws an exception if an unsupported code will be set
     */
    public function testSetUnsupportedCodeWillFail(): void
    {
        $this->expectException(UnsupportedResponseCodeException::class);

        $response = new mockResponse();
        $response->setCode(1);
    }

    /**
     * Tests if the Response returns the correct fields if a supported code is set.
     * 
     * @dataProvider supportedCodeProvider
     */
    public function testSetSupportedCode(int $code): void
    {
        $response = new mockResponse();
        $response->setCode($code);

        $this->assertEquals($code, $response->getCode());
        $this->assertEmpty($response->getCookies());
        $this->assertEmpty($response->getHeaders());
        $this->assertEmpty($response->getJson());
    }

    public function supportedCodeProvider(): array
    {
        return [
            [200],
            [201],
            [204],
            [400],
            [401],
            [403],
            [404],
            [405],
            [406],
            [501]
        ];
    }

    /**
     * Tests if the Response returns only the second code, when 2 were set.
     */
    public function testSetTwoCodes(): void
    {
        $response = new mockResponse();
        $response->setCode((200));
        $response->setCode((201));

        $this->assertEquals(201, $response->getCode());
        $this->assertEmpty($response->getCookies());
        $this->assertEmpty($response->getHeaders());
        $this->assertEmpty($response->getJson());
    }

    /**
     * Tests if the Response returns the correct fields if a Cookie was added.
     */
    public function testAddCookie(): void
    {
        $response = new mockResponse();

        $cookie = $this->createMock(ResponseCookieInterface::class);
        $cookie->expects($this->once)->method("getName")->willReturn("cookie1");
        $response->addCookie($cookie);

        $this->assertEquals(501, $response->getCode());
        $this->assertEmpty($response->getHeaders());
        $this->assertEmpty($response->getJson());

        $this->assertEquals(1, sizeof($response->getCookies()));
        $this->assertEquals($cookie, $response->getCookies()[0]);
    }

    /**
     * Tests if the Response returns the correct fields if 2 Cookies were added.
     */
    public function testAddTwoCookies(): void
    {
        $response = new mockResponse();

        $cookie = $this->createMock(ResponseCookieInterface::class);
        $cookie->expects($this->once)->method("getName")->willReturn("cookie1");
        $response->addCookie($cookie);

        $cookie2 = $this->createMock(ResponseCookieInterface::class);
        $cookie2->expects($this->once)->method("getName")->willReturn("cookie2");
        $response->addCookie($cookie2);

        $this->assertEquals(501, $response->getCode());
        $this->assertEmpty($response->getHeaders());
        $this->assertEmpty($response->getJson());

        $this->assertEquals(2, sizeof($response->getCookies()));
        $this->assertEqualsCanonicalizing([$cookie, $cookie2], $response->getCookies());
    }

    /**
     * Tests if the Response returns only the second cookie if 2 Cookies with the same name were added.
     * 
     * Also test, that the name is not case sensitive
     */
    public function testAddTwoIdenticalCookies(): void
    {
        $response = new mockResponse();

        $cookie = $this->createMock(ResponseCookieInterface::class);
        $cookie->expects($this->once)->method("getName")->willReturn("cookie1");
        $response->addCookie($cookie);

        $cookie2 = $this->createMock(ResponseCookieInterface::class);
        $cookie2->expects($this->once)->method("getName")->willReturn("cOOkie1");
        $response->addCookie($cookie2);

        $this->assertEquals(501, $response->getCode());
        $this->assertEmpty($response->getHeaders());
        $this->assertEmpty($response->getJson());

        $this->assertEquals(1, sizeof($response->getCookies()));
        $this->assertEquals($cookie2, $response->getCookies()[0]);
    }

    /**
     * Tests if the Response throws an exception if the added header is not supported
     */
    public function testAddUnsupportedHeaderFails(): void
    {
        $this->expectException(UnsupportedResponseHeaderException::class);

        $response = new mockResponse();
        $response->addHeader("quatsch", "value");
    }

    /**
     * Tests if the Response returns the correct fields if an header was set.
     * 
     * @dataProvider supportedHeadersProvider
     */
    public function testAddSupportedHeader(string $headerName): void
    {
        $response = new mockResponse();

        $response->addHeader($headerName, "value");

        $this->assertEquals(501, $response->getCode());
        $this->assertEmpty($response->getCookies());
        $this->assertEmpty($response->getJson());

        $this->assertEquals(1, sizeof($response->getHeaders()));
        $this->assertEquals("value", $response->getHeaders()[$headerName]);
    }

    public function supportedHeadersProvider(): array
    {
        return [
            ["Content-Type"],
            ["Content-Length"],
            ["Last-Modified"]
        ];
    }

    /**
     * Tests if the Response returns both headers if 2 were set.
     */
    public function testAddTwoHeaders(): void
    {
        $response = new mockResponse();

        $response->addHeader("Content-Type", "value1");
        $response->addHeader("Content-Length", "value2");

        $this->assertEquals(501, $response->getCode());
        $this->assertEmpty($response->getCookies());
        $this->assertEmpty($response->getJson());

        $this->assertEquals(2, sizeof($response->getHeaders()));
        $this->assertEquals("value1", $response->getHeaders()["Content-Type"]);
        $this->assertEquals("value2", $response->getHeaders()["Content-Length"]);
    }

    /**
     * Tests if the Response returns only the second header if 2 with the same name were added
     */
    public function testAddTwoHeadersWithSameName(): void
    {
        $response = new mockResponse();

        $response->addHeader("Content-Type", "value1");
        $response->addHeader("Content-Type", "value2");

        $this->assertEquals(501, $response->getCode());
        $this->assertEmpty($response->getCookies());
        $this->assertEmpty($response->getJson());

        $this->assertEquals(1, sizeof($response->getHeaders()));
        $this->assertEquals("value2", $response->getHeaders()["Content-Length"]);
    }

    /**
     * Tests if the Response returns the correct fields if data was set.
     */
    public function testSetData(): void
    {
        $response = new mockResponse();

        $response->setData(["test-string" => "Das ein Test!", "test-number" => 42]);

        $this->assertEquals(501, $response->getCode());
        $this->assertEmpty($response->getCookies());
        $this->assertEmpty($response->getJson());
        $this->assertEquals(2, sizeof($response->getHeaders()));
        $this->assertEquals("57", $response->getHeaders()["Content-Length"]);
        $this->assertEquals("application/json;charset=UTF-8", $response->getHeaders()["Content-Type"]);

        $this->assertEquals('{
            "test-string": "Das ein Test!",
            "test-number": 42
          }', $response->getJson());
    }

    /**
     * Tests if the Response returns the second data if it was set twice.
     */
    public function testSetDataTwice(): void
    {
        $response = new mockResponse();

        $response->setData(["test" => 42]);

        $response->setData(["test-string" => "Das ein Test!", "test-number" => 42]);

        $this->assertEquals(501, $response->getCode());
        $this->assertEmpty($response->getCookies());
        $this->assertEmpty($response->getJson());
        $this->assertEquals(2, sizeof($response->getHeaders()));
        $this->assertEquals("57", $response->getHeaders()["Content-Length"]);
        $this->assertEquals("application/json;charset=UTF-8", $response->getHeaders()["Content-Type"]);

        $this->assertEquals('{
            "test-string": "Das ein Test!",
            "test-number": 42
          }', $response->getJson());
    }
}
