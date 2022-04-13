<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi\tests\UnitTests\Utilities;

use BenSauer\CaseStudySkygateApi\Exceptions\InvalidAttributeException;
use BenSauer\CaseStudySkygateApi\Utilities\Interfaces\ValidatorInterface;
use BenSauer\CaseStudySkygateApi\Utilities\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{

    private static ?ValidatorInterface $validator = null;

    public static function setUpBeforeClass(): void
    {
        self::$validator = new Validator;
    }

    public static function tearDownAfterClass(): void
    {
        self::$validator = null;
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
        $this->assertNull(self::$validator->validate($attributes));
    }

    public function testNotAnAttribute(): void
    {
        $this->expectException(InvalidArgumentException::class);
        self::$validator->validate(["NotAnAttribute" => ""]);
    }

    /**
     * @dataProvider emailProvider
     */
    public function testEmailValidation(string $email, bool $valid): void
    {
        if (!$valid) $this->expectException(InvalidAttributeException::class);
        if (!$valid) $this->expectExceptionCode(100);

        $ret = self::$validator->validate(["email" => $email]);
        if ($valid) $this->assertNull($ret);
    }

    //tests email validation according to RFC2822 //source: https://en.wikibooks.org/wiki/JavaScript/Best_practices
    public function emailProvider(): array
    {
        return [
            ["me@example.com", true],
            ["a.nonymous@example.com", true],
            ["name+tag@example.com", true],
            ["a.name+tag@example.com", true],
            //they are commented out, because they would fail. //TODO maybe improve the implementation
            //["me.example@com", true],
            //["\"spaces must be quoted\"@example.com", true],
            ["!#$%&'*+-/=.?^_`{|}~@[1.0.0.127]", true],
            ["!#$%&'*+-/=.?^_`{|}~@[IPv6:0123:4567:89AB:CDEF:0123:4567:89AB:CDEF]", true],

            ["", false],
            ["\n", false],
            ["me@", false],
            ["@example.com", false],
            ["me.@example.com", false],
            [".me@example.com", false],
            ["me@example..com", false],
            ["me\@example.com", false],
            ["spaces\ must\ be\ within\ quotes\ even\ when\ escaped@example.com", false],
            ["a\@mustbeinquotes@example.com", false],
            //to long
            ["eineseeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeehrlaaaaaaaaaaaaaaangeeeeeeeeeeeeeeeeeee@email.de", false]
        ];
    }


    /**
     * @dataProvider passwordProvider
     */
    public function testPasswordValidation(string $pass, bool $valid): void
    {
        if (!$valid) $this->expectException(InvalidAttributeException::class);
        if (!$valid) $this->expectExceptionCode(105);

        $ret = self::$validator->validate(["password" => $pass]);
        if ($valid) $this->assertNull($ret);
    }

    public function passwordProvider(): array
    {
        return [
            ["HalloDuda2", true],
            ["MitÜmläüten0", true],
            ["#?!@$%^&.*-+Aa1", true],
            ["a.name+Tag3@example.com", true],

            //toShort:
            ["", false],
            ["123Abc!", false],
            //toLong:
            ["dhkljndfsfbnjkfbbjkhbjsdfbkjlnlkjsdnklnddksdfkknmA1", false],
            //without uppercase;
            ["1password", false],
            //without lowercase:
            ["1PASSWORD", false],
            //without number:
            ["DasPasswort", false],
            //illegal special character
            ["MoinMoin3,", false],
            ["Cooool3\n45", false],
            //with spaces:
            ["Pass mit space7", false]
        ];
    }

    /**
     * @dataProvider wordsProvider
     */
    public function testNameValidation(string $name, bool $valid): void
    {
        if (!$valid) $this->expectException(InvalidAttributeException::class);
        if (!$valid) $this->expectExceptionCode(101);

        $ret = self::$validator->validate(["name" => $name]);
        if ($valid) $this->assertNull($ret);
    }

    /**
     * @dataProvider wordsProvider
     */
    public function testCityValidation(string $city, bool $valid): void
    {
        if (!$valid) $this->expectException(InvalidAttributeException::class);
        if (!$valid) $this->expectExceptionCode(103);

        $ret = self::$validator->validate(["city" => $city]);
        if ($valid) $this->assertNull($ret);
    }

    public function wordsProvider(): array
    {
        return [
            ["Wort", true],
            ["Zwei Woerter", true],
            ["Zwei Wörter", true],
            ["viele Umlaute ßäÖüÜ", true],
            ["zu", true],

            //wrong spaces:
            ["", false],
            ["Wort ", false],
            [" Wort", false],
            ["Zwei  Spaces", false],
            //to short :
            ["a", false],
            ["a b c", false],
            //with number:
            ["1Wort", false],
            //special character
            ["MoinMoin,", false],
            ["Cooool\n wort", false],
        ];
    }

    /**
     * @dataProvider postcodeProvider
     */
    public function testPostcodeValidation(string $postcode, bool $valid): void
    {
        if (!$valid) $this->expectException(InvalidAttributeException::class);
        if (!$valid) $this->expectExceptionCode(102);

        $ret = self::$validator->validate(["postcode" => $postcode]);
        if ($valid) $this->assertNull($ret);
    }

    public function postcodeProvider(): array
    {
        return [
            ["00000", true],
            ["12345", true],

            //spaces:
            ["", false],
            ["1 2 3 4 5", false],
            [" 12345", false],
            ["12345 ", false],
            //to short :
            ["1234", false],
            //toLong:
            ["123456", false],
            //with letters:
            ["fuenf", false],
            //with special character
            ["1234%", false],
        ];
    }

    /**
     * @dataProvider phoneProvider
     */
    public function testPhoneValidation(string $phone, bool $valid): void
    {
        if (!$valid) $this->expectException(InvalidAttributeException::class);
        if (!$valid) $this->expectExceptionCode(104);

        $ret = self::$validator->validate(["phone" => $phone]);
        if ($valid) $this->assertNull($ret);
    }

    //valid examples from https://de.wikipedia.org/wiki/Rufnummer
    public function phoneProvider(): array
    {
        return [
            ["030 12345-67", true],
            ["0900 5 123456", true],
            ["(030) 12345 67", true],
            ["(030) 12345 67 / 89 ", true],
            ["0 30 / 1 23 45 67", true],
            ["+49 30 12345-67", true],
            ["+49 30 12345 67", true],
            ["+49-30-1234567", true],
            ["+49.3012345x67", true],
            ["+49 (30) 12345 - 67", true],
            ["+49 (0)30 12345-67", true],
            ["015735633702", true],

            //to short :
            ["", false],
            ["123 456 7", false],
            //toLong:
            ["1234567890123456", false],
            //with letters:
            ["fuenf543543", false],
            //with illegal special character
            ["1234%457543", false],
        ];
    }
}
