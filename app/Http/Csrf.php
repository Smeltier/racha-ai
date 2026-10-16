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
}

