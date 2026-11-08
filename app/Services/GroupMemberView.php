<?php

namespace App\Services;

use App\Models\Group\GroupMember;

final readonly class GroupMemberView
{
    public function __construct(
        public GroupMember $member,
        public string $userName,
    ) {}
}
