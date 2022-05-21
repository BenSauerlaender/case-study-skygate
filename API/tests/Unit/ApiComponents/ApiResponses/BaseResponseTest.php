<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Objects\Responses;

use BenSauer\CaseStudySkygateApi\Exceptions\ResponseExceptions\UnsupportedResponseCodeException;
use BenSauer\CaseStudySkygateApi\Exceptions\ResponseExceptions\UnsupportedResponseHeaderException;
use BenSauer\CaseStudySkygateApi\Objects\Cookies\Interfaces\CookieInterface;
use BenSauer\CaseStudySkygateApi\Objects\Responses\BaseResponse;
use PHPUnit\Framework\TestCase;

class mockResponse extends BaseResponse
{
    public function setCode(int $code): void
    {
        parent::setCode($code);
    }

    public function addCookie(CookieInterface
    $cookie): void
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

    public function addMessage(string $msg): void
    {
        parent::addMessage($msg);
    }

    public function addErrorCode(int $code): void
    {
        parent::addErrorCode($code);
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

        $this->assertEquals(500, $response->getCode());
        $this->assertEquals(0, sizeof($response->getCookies()));
        $this->assertEquals(0, sizeof($response->getHeaders()));
        $this->assertEquals(0, strlen($response->getJsonString()));
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
        $this->assertEquals(0, sizeof($response->getCookies()));
        $this->assertEquals(0, sizeof($response->getHeaders()));
        $this->assertEquals(0, strlen($response->getJsonString()));
    }

    public function supportedCodeProvider(): array
    {
        return [
            [200],
            [201],
            [204],
            [303],
            [400],
            [401],
            [403],
            [404],
            [405],
            [406],
            [500]
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
        $this->assertEquals(0, sizeof($response->getCookies()));
        $this->assertEquals(0, sizeof($response->getHeaders()));
        $this->assertEquals(0, strlen($response->getJsonString()));
    }

    /**
     * Tests if the Response returns the correct fields if a Cookie was added.
     */
    public function testAddCookie(): void
    {
        $response = new mockResponse();

        $cookie = $this->createMock(CookieInterface::class);
        $cookie->expects($this->once())->method("getName")->willReturn("cookie1");
        $response->addCookie($cookie);

        $this->assertEquals(500, $response->getCode());
        $this->assertEquals(0, sizeof($response->getHeaders()));
        $this->assertEquals(0, strlen($response->getJsonString()));

        $this->assertEquals(1, sizeof($response->getCookies()));
        $this->assertEquals($cookie, $response->getCookies()[0]);
    }

    /**
     * Tests if the Response returns the correct fields if 2 Cookies were added.
     */
    public function testAddTwoCookies(): void
    {
        $response = new mockResponse();

        $cookie = $this->createMock(CookieInterface::class);
        $cookie->expects($this->once())->method("getName")->willReturn("cookie1");
        $response->addCookie($cookie);

        $cookie2 = $this->createMock(CookieInterface::class);
        $cookie2->expects($this->once())->method("getName")->willReturn("cookie2");
        $response->addCookie($cookie2);

        $this->assertEquals(500, $response->getCode());
        $this->assertEquals(0, sizeof($response->getHeaders()));
        $this->assertEquals(0, strlen($response->getJsonString()));

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

        $cookie = $this->createMock(CookieInterface::class);
        $cookie->expects($this->once())->method("getName")->willReturn("cookie1");
        $response->addCookie($cookie);

        $cookie2 = $this->createMock(CookieInterface::class);
        $cookie2->expects($this->once())->method("getName")->willReturn("cOOkie1");
        $response->addCookie($cookie2);

        $this->assertEquals(500, $response->getCode());
        $this->assertEquals(0, sizeof($response->getHeaders()));
        $this->assertEquals(0, strlen($response->getJsonString()));

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

        $this->assertEquals(500, $response->getCode());
        $this->assertEquals(0, sizeof($response->getCookies()));
        $this->assertEquals(0, strlen($response->getJsonString()));

        $this->assertEquals(1, sizeof($response->getHeaders()));
        $this->assertEquals("value", $response->getHeaders()[$headerName]);
    }

    public function supportedHeadersProvider(): array
    {
        return [
            ["Content-Type"],
            ["Last-Modified"],
            ["Location"]
        ];
    }

    /**
     * Tests if the Response returns both headers if 2 were set.
     */
    public function testAddTwoHeaders(): void
    {
        $response = new mockResponse();

        $response->addHeader("Content-Type", "value1");
        $response->addHeader("Last-Modified", "value2");

        $this->assertEquals(500, $response->getCode());
        $this->assertEquals(0, sizeof($response->getCookies()));
        $this->assertEquals(0, strlen($response->getJsonString()));

        $this->assertEquals(2, sizeof($response->getHeaders()));
        $this->assertEquals("value1", $response->getHeaders()["Content-Type"]);
        $this->assertEquals("value2", $response->getHeaders()["Last-Modified"]);
    }

    /**
     * Tests if the Response returns only the second header if 2 with the same name were added
     */
    public function testAddTwoHeadersWithSameName(): void
    {
        $response = new mockResponse();

        $response->addHeader("Content-Type", "value1");
        $response->addHeader("Content-Type", "value2");

        $this->assertEquals(500, $response->getCode());
        $this->assertEquals(0, sizeof($response->getCookies()));
        $this->assertEquals(0, strlen($response->getJsonString()));

        $this->assertEquals(1, sizeof($response->getHeaders()));
        $this->assertEquals("value2", $response->getHeaders()["Content-Type"]);
    }

    /**
     * Tests if the Response returns the correct fields if data was set.
     */
    public function testSetData(): void
    {
        $response = new mockResponse();

        $response->setData(["test-string" => "Das ein Test!", "test-number" => 42]);

        $this->assertEquals(500, $response->getCode());
        $this->assertEquals(0, sizeof($response->getCookies()));

        $this->assertEquals(1, sizeof($response->getHeaders()));
        $this->assertEquals("application/json;charset=UTF-8", $response->getHeaders()["Content-Type"]);

        $this->assertEquals('{"test-string":"Das ein Test!","test-number":42}', $response->getJsonString());
    }

    /**
     * Tests if the Response returns the correct fields if a message added set.
     */
    public function testAddMessage(): void
    {
        $response = new mockResponse();

        $response->addMessage("test 123.");
        $this->assertEquals(500, $response->getCode());
        $this->assertEquals(0, sizeof($response->getCookies()));

        $this->assertEquals(1, sizeof($response->getHeaders()));
        $this->assertEquals("application/json;charset=UTF-8", $response->getHeaders()["Content-Type"]);

        $this->assertEquals('{"msg":"test 123."}', $response->getJsonString());
    }

    /**
     * Tests if the Response returns the correct fields if a message was added twice.
     */
    public function testAddMessageTwice(): void
    {
        $response = new mockResponse();

        $response->addMessage("test 345.");
        $response->addMessage("test 123.");
        $this->assertEquals(500, $response->getCode());
        $this->assertEquals(0, sizeof($response->getCookies()));

        $this->assertEquals(1, sizeof($response->getHeaders()));
        $this->assertEquals("application/json;charset=UTF-8", $response->getHeaders()["Content-Type"]);

        $this->assertEquals('{"msg":"test 123."}', $response->getJsonString());
    }

    /**
     * Tests if the Response returns the correct fields if a errorCode was added.
     */
    public function testAddErrorCode(): void
    {
        $response = new mockResponse();

        $response->addErrorCode(1);
        $this->assertEquals(500, $response->getCode());
        $this->assertEquals(0, sizeof($response->getCookies()));

        $this->assertEquals(1, sizeof($response->getHeaders()));
        $this->assertEquals("application/json;charset=UTF-8", $response->getHeaders()["Content-Type"]);

        $this->assertEquals('{"code":1}', $response->getJsonString());
    }

    /**
     * Tests if the Response returns the correct fields if a errorCode was added twice.
     */
    public function testAddErrorCodeTwice(): void
    {
        $response = new mockResponse();

        $response->addErrorCode(2);
        $response->addErrorCode(1);
        $this->assertEquals(500, $response->getCode());
        $this->assertEquals(0, sizeof($response->getCookies()));

        $this->assertEquals(1, sizeof($response->getHeaders()));
        $this->assertEquals("application/json;charset=UTF-8", $response->getHeaders()["Content-Type"]);

        $this->assertEquals('{"code":1}', $response->getJsonString());
    }

    /**
     * Tests if the Response returns the second data if it was set twice.
     */
    public function testSetDataTwice(): void
    {
        $response = new mockResponse();

        $response->setData(["test" => 42]);

        $response->setData(["test-string" => "Das ein Test!", "test-number" => 42]);

        $this->assertEquals(500, $response->getCode());
        $this->assertEquals(0, sizeof($response->getCookies()));

        $this->assertEquals(1, sizeof($response->getHeaders()));
        $this->assertEquals("application/json;charset=UTF-8", $response->getHeaders()["Content-Type"]);

        $this->assertEquals('{"test-string":"Das ein Test!","test-number":42}', $response->getJsonString());
    }

    /**
     * Tests if the Response returns the correct data if it was set + a message + an errorCode.
     */
    public function testSetDataAddMessageAddCode(): void
    {
        $response = new mockResponse();


        $response->setData(["test-string" => "Das ein Test!", "test-number" => 42]);
        $response->addMessage("test 123.");
        $response->addErrorCode(1);

        $this->assertEquals(500, $response->getCode());
        $this->assertEquals(0, sizeof($response->getCookies()));

        $this->assertEquals(1, sizeof($response->getHeaders()));
        $this->assertEquals("application/json;charset=UTF-8", $response->getHeaders()["Content-Type"]);

        $this->assertEquals('{"test-string":"Das ein Test!","test-number":42,"msg":"test 123.","code":1}', $response->getJsonString());
    }

    /**
     * Tests if the Responses toString method works correct
     */
    public function testToStringSimple(): void
    {
        $response = new mockResponse();

        $this->assertEquals('500: ', "$response");
    }

    /**
     * Tests if the Responses toString method works correct
     */
    public function testToStringComplex(): void
    {
        $response = new mockResponse();

        $cookie = $this->createMock(CookieInterface::class);
        $cookie->expects($this->once())->method("getName")->willReturn("cookie1");
        $response->addCookie($cookie);

        $response->setData(["test-string" => "Das ein Test!", "test-number" => 42]);
        $response->addMessage("test 123.");
        $response->addErrorCode(1);

        $this->assertEquals('500: set-cookies: cookie1, headers: Content-Type, data: {"test-string":"Das ein Test!","test-number":42,"msg":"test 123.","code":1}', "$response");
    }
}
