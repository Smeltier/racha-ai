<?php

namespace App\Controllers;

use App\Services\GroupService;
use App\Services\UserService;

final class GroupController
{
    public function __construct(
        private UserService $userService
    ) {}
}