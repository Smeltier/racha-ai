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

        $stmt->bindValue(':invited_user_id', $invite->getInvitedUserId(), PDO::PARAM_INT);
        $stmt->bindValue(':group_id', $invite->getGroupId(), PDO::PARAM_INT);
        $stmt->bindValue(':status', $invite->getStatus()->value, PDO::PARAM_STR);
        $stmt->bindValue(':created_at', $invite->getCreatedAt()->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', $invite->getExpiresAt()->format('Y-m-d H:i:s'), PDO::PARAM_STR);

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

        $stmt->bindValue(':status', $invite->getStatus()->value, PDO::PARAM_STR);
        $stmt->bindValue(':id', $invite->getId(), PDO::PARAM_INT);

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

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

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

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

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

        $stmt->bindValue(':invitedUserId', $invitedUserId, PDO::PARAM_INT);

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

        $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);

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

        $stmt->bindValue(':invitedUserId', $invitedUserId, PDO::PARAM_INT);
        $stmt->bindValue(':status', $status->value, PDO::PARAM_STR);

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