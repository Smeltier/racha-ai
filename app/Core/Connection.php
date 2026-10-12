<?php

namespace App\Core;

use PDO;

final class Connection
{
    private function __construct() {}

    public static function create(array $config) : PDO
    {
        $dsn = "mysql:host={$config['host']};dbname={$config['name']};charset=utf8mb4";

        return new PDO(
            $dsn,
            $config['user'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
}

?>
