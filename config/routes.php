<?php

use App\Controllers\UserController;

return [
    ['GET', '/', UserController::class, 'showLogin'],
    ['GET', '/register', UserController::class, 'showRegister'],
    ['POST', '/register', UserController::class, 'register'],
    ['GET', '/login', UserController::class, 'showLogin'],
    ['POST', '/login', UserController::class, 'login'],
    ['POST', '/logout', UserController::class, 'logout'],
    ['GET', '/profile', UserController::class, 'showProfile'],
    ['POST', '/profile', UserController::class, 'updateProfile'],
    ['POST', '/profile/password', UserController::class, 'updatePassword'],
    ['POST', '/profile/delete', UserController::class, 'delete'],
];
