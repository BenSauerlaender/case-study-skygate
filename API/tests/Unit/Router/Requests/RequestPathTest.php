<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Router\Requests;

use BenSauer\CaseStudySkygateApi\Exceptions\InvalidRequestPathException;
use BenSauer\CaseStudySkygateApi\Router\Requests\RequestPath;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the RequestPath method
 */
final class RequestPathTest extends TestCase
{
    /**
     * Tests if the method throws the right exception if the input is not a valid path
     * 
     * @dataProvider invalidRequestPathProvider
     */
    public function testRequestPathConstructionFailsByInvalidPath($input): void
    {
        $this->expectException(InvalidRequestPathException::class);
        new RequestPath($input);
    }

    public function invalidRequestPathProvider(): array
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
     * @dataProvider RequestPathProvider
     */
    public function testRequestPathSuccessful(string $in, array $exp): void
    {
        $return = (new RequestPath($in))->getArray();
        $this->assertEquals($return, $exp);
    }

    public function RequestPathProvider(): array
    {
        return [
            ["test", ["test"]],
            ["/test", ["test"]],
            ["test/", ["test"]],
            ["/test/123/test", ["test", "123", "test"]]
        ];
    }
}
