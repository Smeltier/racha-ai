<?php

namespace App\Models\User;

use InvalidArgumentException;

final class Email
{
    private string $value;

    private function __construct(string $value)
    {
        $value = strtolower(trim($value));

        if (!filter_var($value, FILTER_VALIDATE_EMAIL) || strlen($value) > 320) {
            throw new InvalidArgumentException('Invalid email');
        }

        $this->value = $value;
    }

    public static function from(string $value) : self
    {
        return new self($value);
    }

    public function getValue() : string
    {
        return $this->value;
    }
}

