<?php

namespace App\Models\Invite;

use DateTimeImmutable;
use LogicException;

final class Invite
{
    private function __construct(
        private ?int $id,
        private int $invitedUserId,
        private int $groupId,
        private InviteStatus $status,
        private DateTimeImmutable $createdAt,
    ) {}

    public static function create(int $invitedUserId, int $groupId) : self
    {
        return new self(null, $invitedUserId, $groupId, InviteStatus::Pending, new DateTimeImmutable());
    }

    public static function restore(int $id, int $invitedUserId, int $groupId, InviteStatus $status, DateTimeImmutable $createdAt) : self
    {
        return new self($id, $invitedUserId, $groupId, $status, $createdAt);
    }

    public function accept() : void
    {
        if ($this->status !== InviteStatus::Pending) {
            throw new LogicException("Cannot accept an invite that is not pending");
        }

        $this->status = InviteStatus::Accepted;
    }

    public function decline() : void
    {
        if ($this->status !== InviteStatus::Pending) {
            throw new LogicException("Cannot decline an invite that is not pending");
        }

        $this->status = InviteStatus::Declined;
    }

    public function expire() : void
    {
        if ($this->status !== InviteStatus::Pending) {
            throw new LogicException("Cannot expire an invite that is not pending");
        }

        $this->status = InviteStatus::Expired;
    }

    public function isPending() : bool
    {
        return $this->status === InviteStatus::Pending;
    }

    public function isExpired() : bool
    {
        return $this->status === InviteStatus::Expired;
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getInvitedUserId() : int
    {
        return $this->invitedUserId;
    }

    public function getGroupId() : int
    {
        return $this->groupId;
    }

    public function getStatus() : InviteStatus
    {
        return $this->status;
    }

    public function getCreatedAt() : DateTimeImmutable
    {
        return $this->createdAt;
    }
}