<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\Controller;

use BenSauer\CaseStudySkygateApi\Controller\Interfaces\ValidationControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\ValidationController;
use PHPUnit\Framework\TestCase;

final class ValidationControllerTest extends TestCase
{

    private static ?ValidationControllerInterface $ValidationController = null;

    public static function setUpBeforeClass(): void
    {
        self::$ValidationController = new ValidationController;
    }

    public static function tearDownAfterClass(): void
    {
        self::$ValidationController = null;
    }

    public function testMultipleValidations(): void
    {
        $attributes = [
            "email" => "test@mail.de",
            "name" => "Ben Sauerländer",
            "postcode" => "01234",
            "city" => "Berlin",
            "phone" => "030 12345-67",
            "password" => "1SicheresPassword"
        ];
        $this->assertTrue(self::$ValidationController->validate($attributes));
    }

    /**
     * Tests the validate throws an exception if at least one field is not supported
     */
    public function testUnsupportedField(): void
    {
        $return = self::$ValidationController->validate(["NotAnField" => ""]);
        $this->assertEquals(["NotAnField" => ["UNSUPPORTED"]], $return);
    }

    /**
     * Tests if the validation throws an exception if the value type is invalid
     */
    public function testInvalidType(): void
    {
        $return = self::$ValidationController->validate(["name" => 123]);
        $this->assertEquals(["name" => ["INVALID_TYPE"]], $return);
    }

    /**
     * Tests validate returns true if the array is empty
     */
    public function testNotAnAttribute(): void
    {
        $this->assertTrue(self::$ValidationController->validate([]));
    }

    private function assertCorrectValidation(string $field, mixed $value, bool $valid, string $reason): void
    {
        $ret = self::$ValidationController->validate([$field => $value]);

        if ($valid) {
            $this->assertTrue($ret);
        } else {
            $this->assertCount(1, $ret);
            $this->assertArrayHasKey($field, $ret);
            //return contains reason
            $this->assertEquals($reason, $ret[$field][0]);
        }
    }

    /**
     * @dataProvider emailProvider
     */
    public function testEmailValidation(string $email, bool $valid, string $reason): void
    {
        $this->assertCorrectValidation("email", $email, $valid, $reason);
    }

    //tests email validation according to RFC2822 //source: https://en.wikibooks.org/wiki/JavaScript/Best_practices
    public function emailProvider(): array
    {
        return [
            ["me@example.com",                                                      true,   ""],
            ["a.nonymous@example.com",                                              true,   ""],
            ["name+tag@example.com",                                                true,   ""],
            ["a.name+tag@example.com",                                              true,   ""],
            //they are commented out, because they would fail. //TODO maybe improve the implementation
            //["me.example@com", true],
            //["\"spaces must be quoted\"@example.com", true],
            ["!#$%&'*+-/=.?^_`{|}~@[1.0.0.127]",                                    true,   "NO_EMAIL"],
            ["!#$%&'*+-/=.?^_`{|}~@[IPv6:0123:4567:89AB:CDEF:0123:4567:89AB:CDEF]", true,   "NO_EMAIL"],

            ["",                                                                    false,  "NO_EMAIL"],
            ["\n",                                                                  false,  "NO_EMAIL"],
            ["me@",                                                                 false,  "NO_EMAIL"],
            ["@example.com",                                                        false,  "NO_EMAIL"],
            ["me.@example.com",                                                     false,  "NO_EMAIL"],
            [".me@example.com",                                                     false,  "NO_EMAIL"],
            ["me@example..com",                                                     false,  "NO_EMAIL"],
            ["me\@example.com",                                                     false,  "NO_EMAIL"],
            ["spaces\ must\ be\ within\ quotes\ even\ when\ escaped@example.com",   false,  "NO_EMAIL"],
            ["a\@mustbeinquotes@example.com",                                       false,  "NO_EMAIL"],
            //to long
            ["eineseeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeehrlaaaaaaaaaaaaaaangeeeeeeeeeeeeeeeeeeeeeeeee@email.de", false, "TO_LONG"]
        ];
    }


    /**
     * @dataProvider passwordProvider
     */
    public function testPasswordValidation(string $pass, bool $valid, string $reason): void
    {
        $this->assertCorrectValidation("password", $pass, $valid, $reason);
    }

    public function passwordProvider(): array
    {
        return [
            ["HalloDuda2",                                              true,   ""],
            ["MitÜmläüten0",                                            true,   ""],
            ["#?!@$%^&.*-+Aa1",                                         true,   ""],
            ["a.name+Tag3@example.com",                                 true,   ""],

            //toShort:
            ["",                                                        false,  "TO_SHORT"],
            ["123Abc!",                                                 false,  "TO_SHORT"],
            //toLong:
            ["dhkljndfsfbnjkfbbjkhbjsdfbkjlnlkjsdnklnddksdfkknmA1",     false,  "TO_LONG"],
            //without uppercase;
            ["1password",                                               false,  "NO_UPPER_CASE"],
            //without lowercase:
            ["1PASSWORD",                                               false,  "NO_LOWER_CASE"],
            //without number:
            ["DasPasswort",                                             false,  "NO_NUMBER"],
            //illegal special character
            ["MoinMoin3,",                                              false,  "INVALID_CHAR"],
            ["Cooool3\n45",                                             false,  "INVALID_CHAR"],
            //with spaces:
            ["Pass mit space7",                                         false,  "INVALID_CHAR"]
        ];
    }

    /**
     * @dataProvider wordsProvider
     */
    public function testNameValidation(string $name, bool $valid, string $reason): void
    {
        $this->assertCorrectValidation("name", $name, $valid, $reason);
    }

    /**
     * @dataProvider wordsProvider
     */
    public function testCityValidation(string $city, bool $valid, string $reason): void
    {
        $this->assertCorrectValidation("city", $city, $valid, $reason);
    }

    public function wordsProvider(): array
    {
        return [
            ["Wort",                true,   ""],
            ["Zwei Woerter",        true,   ""],
            ["Zwei Wörter",         true,   ""],
            ["viele Umlaute ßäÖüÜ", true,   ""],
            ["zu",                  true,   ""],

            //wrong spaces:
            ["",                    false,  "NO_WORDS"],
            ["Wort ",               false,  "NO_WORDS"],
            [" Wort",               false,  "NO_WORDS"],
            ["Zwei  Spaces",        false,  "NO_WORDS"],
            //to short :
            ["a",                   false,  "NO_WORDS"],
            ["a b c",               false,  "NO_WORDS"],
            //with number:
            ["1Wort",               false,  "NO_WORDS"],
            //special character
            ["MoinMoin,",           false,  "NO_WORDS"],
            ["Cooool\n wort",       false,  "NO_WORDS"],
        ];
    }

    /**
     * @dataProvider postcodeProvider
     */
    public function testPostcodeValidation(string $postcode, bool $valid, string $reason): void
    {
        $this->assertCorrectValidation("postcode", $postcode, $valid, $reason);
    }

    public function postcodeProvider(): array
    {
        return [
            ["00000",       true,   ""],
            ["12345",       true,   ""],

            //spaces:
            ["",            false,  "INVALID_LENGTH"],
            ["1 2 3 4 5",   false,  "INVALID_LENGTH"],
            [" 12345",      false,  "INVALID_LENGTH"],
            ["12345 ",      false,  "INVALID_LENGTH"],
            //to short :
            ["1234",        false,  "INVALID_LENGTH"],
            //toLong
            ["123456",      false,  "INVALID_LENGTH"],
            //with letters:
            ["fuenf",       false,  "INVALID_CHAR"],
            //with special character
            ["1234%",       false,  "INVALID_CHAR"],
        ];
    }

    /**
     * @dataProvider phoneProvider
     */
    public function testPhoneValidation(string $phone, bool $valid, string $reason): void
    {
        $this->assertCorrectValidation("phone", $phone, $valid, $reason);
    }

    //valid examples from https://de.wikipedia.org/wiki/Rufnummer
    public function phoneProvider(): array
    {
        return [
            ["030 12345-67",            true,   ""],
            ["0900 5 123456",           true,   ""],
            ["(030) 12345 67",          true,   ""],
            ["(030) 12345 67 / 89 ",    true,   ""],
            ["0 30 / 1 23 45 67",       true,   ""],
            ["+49 30 12345-67",         true,   ""],
            ["+49 30 12345 67",         true,   ""],
            ["+49-30-1234567",          true,   ""],
            ["+49.3012345x67",          true,   ""],
            ["+49 (30) 12345 - 67",     true,   ""],
            ["+49 (0)30 12345-67",      true,   ""],
            ["015735633702",            true,   ""],

            //to short :
            ["",                        false,  "TO_SHORT"],
            ["123 456 7",               false,  "TO_SHORT"],
            //toLong:
            ["1234567890123456",        false,  "TO_LONG"],
            //with letters:
            ["fuenf543543543534",       false,  "INVALID_CHAR"],
            //with illegal special character
            ["1234%457543",             false,  "INVALID_CHAR"],
        ];
    }
}
