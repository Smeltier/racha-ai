<?php

namespace App\Models\Group;

use DateTimeImmutable;

final class GroupMember
{
    public readonly int $userId;
    public readonly GroupRole $role;
    public readonly DateTimeImmutable $joinedAt;

    public function __construct(int $userId, GroupRole $role, DateTimeImmutable $joinedAt)
    {
        $this->userId = $userId;
        $this->role = $role;
        $this->joinedAt = $joinedAt;
    }

    public function isAdmin() : bool
    {
        return $this->role === GroupRole::Admin;
    }

    public function withRole(GroupRole $role) : self
    {
        return new self($this->userId, $role, $this->joinedAt);
    }
}
