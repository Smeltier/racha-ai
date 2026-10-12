<?php

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('America/Sao_Paulo');

use App\Http\Session;
use App\Core\Connection;
use App\Controllers\UserController;
use App\Models\User\UserDAO;
use App\Services\UserService;

$config = require __DIR__ . '/../config/database.php';

$session = new Session();
$session->start();

$pdo = Connection::create($config);
$userService = new UserService(new UserDAO($pdo));
$userController = new UserController($userService, $session);

$routes = [
    'GET' => [
        '/' => [$userController, 'showLogin'],
        '/register' => [$userController, 'showRegister'],
        '/login' => [$userController, 'showLogin'],
        '/profile' => [$userController, 'showProfile'],
    ],
    'POST' => [
        '/register' => [$userController, 'register'],
        '/login' => [$userController, 'login'],
        '/logout' => [$userController, 'logout'],
        '/profile' => [$userController, 'updateProfile'],
        '/profile/password' => [$userController, 'updatePassword'],
        '/profile/delete' => [$userController, 'delete'],
    ],
];

$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

$handler = $routes[$method][$path] ?? null;

if ($handler === null) {
    http_response_code(404);
    echo 'Page not found';
    exit;
}

$handler();

