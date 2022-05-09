<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Unit\Utilities\ApiUtilities;

use BenSauer\CaseStudySkygateApi\Exceptions\InvalidApiPathException;
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
        $SERVER["HTTPS"] = 1;
        $SERVER["REQUEST_URI"] = "/path/to/x";

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

        $this->expectException(InvalidApiPathException::class);

        ApiUtilities::getRequest($SERVER, [], "/path/to");
    }
}
