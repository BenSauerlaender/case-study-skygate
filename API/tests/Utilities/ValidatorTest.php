<?php

declare(strict_types=1);

use BenSauer\CaseStudySkygateApi\Utilities\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    /**
     * @dataProvider emailProvider
     */
    public function testIsEmail(string $email, bool $expected): void
    {
        $this->assertSame($expected, Validator::isEmail($email));
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
    public function testIsPassword(string $pass, bool $expected): void
    {
        $this->assertSame($expected, Validator::isPassword($pass));
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
    public function testIsWords(string $words, bool $expected): void
    {
        $this->assertSame($expected, Validator::isWords($words));
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
            //withnumber:
            ["1Wort", false],
            //special character
            ["MoinMoin,", false],
            ["Cooool\n wort", false],
        ];
    }

    /**
     * @dataProvider postcodeProvider
     */
    public function testIsPostcode(string $postcode, bool $expected): void
    {
        $this->assertSame($expected, Validator::isPostcode($postcode));
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
    public function testIsPhonenumber(string $phonenumber, bool $expected): void
    {
        $this->assertSame($expected, Validator::isPhonenumber($phonenumber));
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
