<?php

namespace App\Http;

final class Session
{
    private const USER_ID_KEY = 'user_id';

    public function start() : void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    public function login(int $userId) : void
    {
        session_regenerate_id(true);
        $_SESSION[self::USER_ID_KEY] = $userId;
    }

    public function logout() : void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'],
                ],
            );
        }

        session_destroy();
    }

    public function userId() : ?int
    {
        $id = $_SESSION[self::USER_ID_KEY] ?? null;
        return is_int($id) ? $id : null;
    }

    public function isLoggedIn() : bool
    {
        return $this->userId() !== null;
    }
}

