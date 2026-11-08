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
    ['GET', '/groups/{id}/edit', GroupController::class, 'showEdit'],
    ['POST', '/groups', GroupController::class, 'create'],
    ['POST', '/groups/{id}', GroupController::class, 'update'],
    ['POST', '/groups/{groupId}/members/{memberId}/remove', GroupController::class, 'removeMember'],
    ['POST', '/groups/{groupId}/members/{memberId}/promote', GroupController::class, 'promoteMember'],
    ['POST', '/groups/{groupId}/members/{memberId}/demote', GroupController::class, 'demoteMember'],
    ['GET', '/invites', InviteController::class, 'showInvites'],
    ['GET', '/groups/{groupId}/invites', InviteController::class, 'showGroupInvites'],
    ['POST', '/groups/{groupId}/invites', InviteController::class, 'invite'],
    ['POST', '/invites/{inviteId}/accept', InviteController::class, 'accept'],
    ['POST', '/invites/{inviteId}/decline', InviteController::class, 'decline'],
    ['POST', '/invites/{inviteId}/cancel', InviteController::class, 'cancel'],
];
