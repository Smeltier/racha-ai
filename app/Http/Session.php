<?php

namespace App\Http;

final class Session
{
    private const USER_ID_KEY = 'user_id';
    private const IDLE_TIMEOUT = 1800;
    private const ABSOLUTE_TIMEOUT = 28800;

    public function start() : void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.gc_maxlifetime', (string) self::ABSOLUTE_TIMEOUT);

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => !empty($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            session_start();
        }

        $now = time();
        if (isset($_SESSION['created_at'])) {
            $idle = $now - ($_SESSION['last_activity'] ?? $now);
            $age = $now - $_SESSION['created_at'];

            if ($idle > self::IDLE_TIMEOUT || $age > self::ABSOLUTE_TIMEOUT) {
                $_SESSION = [];
                session_regenerate_id(true);
                return;
            }

            $_SESSION['last_activity'] = $now;
        }

    }

    public function login(int $userId) : void
    {
        session_regenerate_id(true);
        $_SESSION[self::USER_ID_KEY] = $userId;
        $_SESSION['created_at'] = time();
        $_SESSION['last_activity'] = time();
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

