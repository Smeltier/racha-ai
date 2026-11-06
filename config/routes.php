<?php

use App\Controllers\UserController;
use App\Controllers\GroupController;
use App\Controllers\InviteController;

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
    ['GET', '/groups', GroupController::class, 'showGroups'],
    ['GET', '/groups/create', GroupController::class, 'showCreate'],
    ['GET', '/groups/{id}', GroupController::class, 'show'],
    ['POST', '/groups', GroupController::class, 'create'],
    ['GET', '/invites', InviteController::class, 'showInvites'],
    ['GET', '/groups/{groupId}/invites', InviteController::class, 'showGroupInvites'],
    ['POST', '/groups/{groupId}/invites', InviteController::class, 'invite'],
    ['POST', '/invites/{id}/accept', InviteController::class, 'accept'],
    ['POST', '/invites/{id}/decline', InviteController::class, 'decline'],
    ['POST', '/invites/{id}/cancel', InviteController::class, 'cancel'],
];
