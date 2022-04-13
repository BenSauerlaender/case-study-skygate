<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\Utilities;

use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\SecurityUtilitiesInterface;
use BenSauer\CaseStudySkygateApi\Utilities\SecurityUtilities;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SecurityUtilitiesTest extends TestCase
{
    private static ?SecurityUtilitiesInterface $passUtils = null;

    public static function setUpBeforeClass(): void
    {
        self::$passUtils = new SecurityUtilities;
    }

    public static function tearDownAfterClass(): void
    {
        self::$passUtils = null;
    }

    /**
     * @dataProvider passwordProvider
     */
    public function testPasswordHashAndVerify(string $pass1, string $pass2): void
    {
        $hash = self::$passUtils->hashPassword($pass1);

        //is 60 characters long
        $this->assertEquals(60, strlen($hash));

        $this->assertTrue(self::$passUtils->checkPassword($pass1, $hash));
        $this->assertNotTrue(self::$passUtils->checkPassword($pass2, $hash));
    }

    public function passwordProvider(): array
    {
        return [
            "password1" => ["1Password", "1AnderesPassword"],
            "password2" => ["&%$/`'$)/$%&))++***/#", "17896"],
            "longPassword" => ["eineseeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeehrlaaaaaaaaaaaaaaangeeeeeeeeeeeeeeeeeeesPassword", "udsgbf"]
        ];
    }

    /**
     * @dataProvider validCodeLengthProvider
     */
    public function testSuccessfulCodeGeneration(int $length): void
    {
        $code = self::$passUtils->generateCode($length);

        //has the right length
        $this->assertEquals($length, strlen($code));

        //contains only Hex-digits
        $this->assertEquals(1, preg_match("/[0-9ABCDEF]*/", $code));
    }

    public function validCodeLengthProvider(): array
    {
        return [[3], [0], [10], [99]];
    }

    /**
     * @dataProvider InvalidCodeLengthProvider
     */
    public function testCodeGenerationFail(int $length): void
    {
        $this->expectException(InvalidArgumentException::class);
        self::$passUtils->generateCode($length);
    }

    public function invalidCodeLengthProvider(): array
    {
        return [[-1], [100]];
    }
}
