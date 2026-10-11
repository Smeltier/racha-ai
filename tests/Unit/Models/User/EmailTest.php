<?php

namespace Tests\Unit\Models\User;

use App\Models\User\Email;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testShouldCreateWithValidValue() : void
    {
        $value = "testemail@gmail.com";
        $email = Email::from($value);
        $this->assertEquals($value, $email->getValue());
    }

    public function testShouldNormalizeTheValue() : void
    {
        $email = Email::from("TESTEMAIL@GMAIL.COM");
        $this->assertEquals("testemail@gmail.com", $email->getValue());
    }

    public function testShouldRemoveBlankSpaces() : void
    {
        $email = Email::from(' testemail@gmail.com ');
        $this->assertEquals('testemail@gmail.com', $email->getValue());
    }

    public function testFromShouldCreateTheSameEmail() : void
    {
        $firstEmail = Email::from("testemail@gmail.com");
        $firstValue = $firstEmail->getValue();

        $secondEmail = Email::from($firstValue);
        $secondValue = $secondEmail->getValue();

        $this->assertEquals($firstValue, $secondValue);
    }

    public static function invalidEmails() : array
    {
        return [
            "blank" => [''],
            "spaces" => ['   '],
            "domain" => ["test@"],
            "local" => ['@gmail.com'],
            "space" => ['tes t.email@gmail.com'],
            "two_@" => ['test.email@@gmail.com'],
            "without_@" => ['testemailgmail.com'],
            "big_local" => [str_repeat('a', 65) . '@gmail.com'],
            "line_break" => ['test\nemail@gmail.com']
        ];
    }

    #[DataProvider('invalidEmails')]
    public function testShouldThrowWhenEmailIsInvalid(string $value) : void
    {
        $this->expectException(InvalidArgumentException::class);
        Email::from($value);
    }
}
