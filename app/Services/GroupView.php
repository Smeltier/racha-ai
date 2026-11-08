<?php

namespace App\Services;

use App\Models\Group\Group;

final readonly class GroupView
{
    public function __construct(
        public Group $group,
        public array $members,
    ) {}
}
