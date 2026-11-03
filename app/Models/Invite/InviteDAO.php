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
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM invites
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $id);

        $stmt->execute();

        $tuple = $stmt->fetch(PDO::FETCH_ASSOC);
        return $tuple ? $this->mapTuple($tuple) : null;
    }

    public function findByInvitedUserId(int $invitedUserId) : array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM invites
            WHERE invited_user_id = :invitedUserId
        ");
        $stmt->bindParam(':invitedUserId', $invitedUserId);

        $stmt->execute();

        $invites = [];
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $tuple) {
            $invites[] = $this->mapTuple($tuple);
        }

        return $invites;
    }

    public function findByGroupId(int $groupId) : array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM invites
            WHERE group_id = :groupId
        ");
        $stmt->bindParam(':groupId', $groupId);

        $stmt->execute();

        $invites = [];
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $tuple) {
            $invites[] = $this->mapTuple($tuple);
        }

        return $invites;
    }

    public function findByInvitedUserIdAndInviteStatus(int $invitedUserId, InviteStatus $status) : array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM invites
            WHERE invited_user_id = :invitedUserId AND status = :status
        ");
        $stmt->bindParam(':invitedUserId', $invitedUserId);
        $stmt->bindParam(':status', $status->value);

        $stmt->execute();

        $invites = [];
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $tuple) {
            $invites[] = $this->mapTuple($tuple);
        }

        return $invites;
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