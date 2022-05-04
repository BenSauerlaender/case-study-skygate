<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\ApiComponents\ApiRequests;

use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestMethodException;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\RequestMethod;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the HttpMethod enum
 */
final class RequestMethodTest extends TestCase
{
    /**
     * Tests if the method throws the right exception if the input is not a valid HttpMethod
     */
    public function testFromStringWithInvalidMethod(): void
    {
        $this->expectException(InvalidRequestMethodException::class);
        RequestMethod::fromString("quatsch");
    }

    /**
     * Tests if the method returns the correct HttpMethod
     * 
     * @dataProvider httpMethodProvider
     */
    public function testFromStringSuccessful(string $in, RequestMethod $exp): void
    {
        $return = RequestMethod::fromString($in);
        $this->assertEquals($return, $exp);
    }

    public function httpMethodProvider(): array
    {
        return [
            ["Get", RequestMethod::GET],
            ["Post", RequestMethod::POST],
            ["Head", RequestMethod::HEAD],
            ["Put", RequestMethod::PUT],
            ["Delete", RequestMethod::DELETE],
            ["Connect", RequestMethod::CONNECT],
            ["Options", RequestMethod::OPTIONS],
            ["Trace", RequestMethod::TRACE],
            ["Patch", RequestMethod::PATCH],
        ];
    }
}
