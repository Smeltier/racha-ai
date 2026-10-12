<?php

namespace App\Controller;

use App\Models\User\User;
use App\Services\UserService;

final class UserController
{
    private UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }
}
