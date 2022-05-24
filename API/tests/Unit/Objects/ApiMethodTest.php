<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Objects;

use BenSauer\CaseStudySkygateApi\Exceptions\RequestExceptions\InvalidMethodException;
use BenSauer\CaseStudySkygateApi\Objects\ApiMethod;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the HttpMethod enum
 */
final class ApiMethodTest extends TestCase
{
    /**
     * Tests if the method throws the right exception if the input is not a valid HttpMethod
     */
    public function testFromStringWithInvalidMethod(): void
    {
        $this->expectException(InvalidMethodException::class);
        ApiMethod::fromString("quatsch");
    }

    /**
     * Tests if the method returns the correct HttpMethod
     * 
     * @dataProvider httpMethodProvider
     */
    public function testFromStringSuccessful(string $in, ApiMethod $exp): void
    {
        $return = ApiMethod::fromString($in);
        $this->assertEquals($return, $exp);
    }

    public function httpMethodProvider(): array
    {
        return [
            ["Get", ApiMethod::GET],
            ["Post", ApiMethod::POST],
            ["Head", ApiMethod::HEAD],
            ["Put", ApiMethod::PUT],
            ["Delete", ApiMethod::DELETE],
            ["Connect", ApiMethod::CONNECT],
            ["Options", ApiMethod::OPTIONS],
            ["Trace", ApiMethod::TRACE],
            ["Patch", ApiMethod::PATCH],
        ];
    }
}
