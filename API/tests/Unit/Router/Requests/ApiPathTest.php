<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Router\Requests;

use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiPathException;
use BenSauer\CaseStudySkygateApi\Router\Requests\ApiPath;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the ApiPath method
 */
final class ApiPathTest extends TestCase
{
    /**
     * Tests if the method throws the right exception if the input is not a valid path
     * 
     * @dataProvider invalidApiPathProvider
     */
    public function testApiPathConstructionFailsByInvalidPath($input): void
    {
        $this->expectException(InvalidApiPathException::class);
        new ApiPath($input);
    }

    public function invalidApiPathProvider(): array
    {
        return [
            "empty string" => [""],
            "empty sub-part" => ["test//test"],
            "invalid character" => ["/test+ding/jo"]
        ];
    }

    /**
     * Tests if the method returns the correct array
     * 
     * @dataProvider ApiPathProvider
     */
    public function testApiPathSuccessful(string $in, array $exp): void
    {
        $return = (new ApiPath($in))->getArray();
        $this->assertEquals($return, $exp);
    }

    public function ApiPathProvider(): array
    {
        return [
            ["test", ["test"]],
            ["/test", ["test"]],
            ["test/", ["test"]],
            ["/test/123/test", ["test", "123", "test"]]
        ];
    }
}
