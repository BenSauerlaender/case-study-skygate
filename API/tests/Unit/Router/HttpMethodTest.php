<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Router;

use BenSauer\CaseStudySkygateApi\Exceptions\InvalidHttpMethodException;
use BenSauer\CaseStudySkygateApi\Router\HttpMethod;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the HttpMethod enum
 */
final class HttpMethodTest extends TestCase
{
    /**
     * Tests if the method throws the right exception if the input is not a valid HttpMethod
     */
    public function testFromStringWithInvalidMethod(): void
    {
        $this->expectException(InvalidHttpMethodException::class);
        HttpMethod::fromString("quatsch");
    }

    /**
     * Tests if the method returns the correct HttpMethod
     * 
     * @dataProvider httpMethodProvider
     */
    public function testFromStringSuccessful(string $in, HttpMethod $exp): void
    {
        $return = HttpMethod::fromString($in);
        $this->assertEquals($return, $exp);
    }

    public function httpMethodProvider(): array
    {
        return [
            ["Get", HttpMethod::GET],
            ["Post", HttpMethod::POST],
            ["Head", HttpMethod::HEAD],
            ["Put", HttpMethod::PUT],
            ["Delete", HttpMethod::DELETE],
            ["Connect", HttpMethod::CONNECT],
            ["Options", HttpMethod::OPTIONS],
            ["Trace", HttpMethod::TRACE],
            ["Patch", HttpMethod::PATCH],
        ];
    }
}
