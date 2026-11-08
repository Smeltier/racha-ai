<?php

namespace App\Services;

use App\Models\Invite\Invite;

final readonly class InviteView
{
    public function __construct(
        public Invite $invite,
        public string $groupName,
        public string $userName,
        public string $userEmail,
    ) {}
}