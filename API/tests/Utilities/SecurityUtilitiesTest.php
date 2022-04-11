<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\SecurityUtilitiesInterface;
use BenSauer\CaseStudySkygateApi\Utilities\SecurityUtilities;
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
    public function passwordHashAndVerifyTest(string $pass1, string $pass2): void
    {
        $hash = self::$passUtils->hashPassword($pass1);
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
}
