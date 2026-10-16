<?php

namespace App\Http;

final class Csrf
{
    private const KEY = 'csrf_token';

    public function token() : string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::KEY];
    }

    public function isValid(?string $token) : bool
    {
        $stored = $_SESSION[self::KEY] ?? '';
        return is_string($token) && $stored !== '' && hash_equals($stored, $token);
    }
}

