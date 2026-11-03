<?php

namespace App\Models\Invite;

final class InviteDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(Invite $invite) : int
    {
    }

    public function update(Invite $invite) : void
    {
    }

    public function delete(int $id) : void
    {
    }

    public function findById(int $id) : ?Invite
    {
    }

    public function findByInvitedUserId(int $invitedUserId) : array
    {
    }

    public function findByGroupId(int $groupId) : array
    {
    }

    public function findByInvitedUserIdAndInviteStatus(int $invitedUserId, InviteStatus $status) : array
    {
    }

    private function mapTuple(array $tuple) : Invite
    {
        return Invite::reconstitute(
            $tuple['id'],
            $tuple['invited_user_id'],
            $tuple['group_id'],
            InviteStatus::from($tuple['status']),
            new DateTimeImmutable($tuple['created_at']),
            new DateTimeImmutable($tuple['expires_at'])
        );
    }
}