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
        private DateTimeImmutable $expiresAt
    ) {}

    public static function create(int $invitedUserId, int $groupId) : self
    {
        return new self(
            null,
            $invitedUserId,
            $groupId,
            InviteStatus::Pending,
            new DateTimeImmutable(),
            new DateTimeImmutable('+7 days')
        );
    }

    public static function restore(int $id, int $invitedUserId, int $groupId, InviteStatus $status, DateTimeImmutable $createdAt, DateTimeImmutable $expiresAt) : self
    {
        $now = new DateTimeImmutable();
        if ($status == InviteStatus::Pending && $expiresAt <= $now) {
            $status = InviteStatus::Expired;
        }

        return new self(
            $id,
            $invitedUserId,
            $groupId,
            $status,
            $createdAt,
            $expiresAt
        );
    }

    public function accept() : void
    {
        $this->expireIfNecessary(new DateTimeImmutable());

        if ($this->status !== InviteStatus::Pending) {
            throw new LogicException("Cannot accept an invite that is not pending");
        }

        $this->status = InviteStatus::Accepted;
    }

    public function decline() : void
    {
        $this->expireIfNecessary(new DateTimeImmutable());

        if ($this->status !== InviteStatus::Pending) {
            throw new LogicException("Cannot decline an invite that is not pending");
        }

        $this->status = InviteStatus::Declined;
    }

    public function expire() : void
    {
        $this->expireIfNecessary(new DateTimeImmutable());

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

    public function isExpiredAt(DateTimeImmutable $dateTime) : bool
    {
        return $this->status === InviteStatus::Expired || ($this->expiresAt <= $dateTime && $this->status === InviteStatus::Pending);
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

    public function getExpiresAt() : DateTimeImmutable
    {
        return $this->expiresAt;
    }

    private function expireIfNecessary(DateTimeImmutable $now) : void
    {
        if ($this->status === InviteStatus::Pending && $this->expiresAt <= $now) {
            $this->status = InviteStatus::Expired;
        }
    }
}