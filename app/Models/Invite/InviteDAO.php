<?php

namespace App\Models\Invite;

use DateTimeImmutable;
use LogicException;
use PDO;
use PDOException;

final class InviteDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(Invite $invite) : void
    {
        if ($invite->getId() !== null) {
            throw new LogicException('Invite already persisted');
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO invites (invited_user_id, group_id, status, created_at, expires_at)
            VALUES (:invited_user_id, :group_id, :status, :created_at, :expires_at)
        ");

        $stmt->bindParam(':invited_user_id', $invite->getInvitedUserId());
        $stmt->bindParam(':group_id', $invite->getGroupId());
        $stmt->bindParam(':status', $invite->getStatus()->value);
        $stmt->bindParam(':created_at', $invite->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindParam(':expires_at', $invite->getExpiresAt()->format('Y-m-d H:i:s'));

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new LogicException('Invite already exists for this user and group');
            }
            throw $e;
        }

        $id = (int) $this->pdo->lastInsertId();
        $invite->assignId($id);
    }

    public function update(Invite $invite) : void
    {
        if ($invite->getId() === null) {
            throw new LogicException('Invite not yet persisted');
        }

        $stmt = $this->pdo->prepare("
            UPDATE invites
            SET status = :status
            WHERE id = :id
        ");

        $stmt->bindParam(':status', $invite->getStatus()->value);
        $stmt->bindParam(':id', $invite->getId());

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function delete(int $id) : void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM invites
            WHERE id = :id
        ");

        $stmt->bindParam(':id', $id);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function findById(int $id) : ?Invite
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM invites
            WHERE id = :id
        ");

        $stmt->bindParam(':id', $id);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }

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

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }

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

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }

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

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }

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