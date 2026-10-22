<?php

require __DIR__ . '/vendor/autoload.php';

use App\Http\Csrf;
use App\Http\Router;
use App\Http\Session;
use App\Core\Connection;

use App\Controllers\UserController;
use App\Controllers\GroupController;
use App\Models\Group\GroupDAO;
use App\Models\User\UserDAO;
use App\Services\GroupService;
use App\Services\UserService;

date_default_timezone_set('America/Sao_Paulo');

$isDev = ($_SERVER['SERVER_NAME'] ?? '') === 'localhost';
ini_set('display_errors', $isDev ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

$config = require __DIR__ . '/config/database.php';
$routes = require __DIR__ . '/config/routes.php';

$session = new Session();
$session->start();

try {
    $pdo = Connection::create($config);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Service unavailable');
}

$userService = new UserService(new UserDAO($pdo));
$groupService = new GroupService(new GroupDAO($pdo));

$factories = [
    UserController::class => fn() => new UserController($userService, $session),
    GroupController::class => fn() => new GroupController($groupService, $session),
];

$router = new Router(
    $routes,
    $factories,
    new Csrf(),
    rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\')
);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
