<?php

namespace App\Models\Invite;

use App\Services\InviteView;
use DateTimeImmutable;
use App\Exceptions\PersistenceException;
use App\Exceptions\StateException;
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
            throw new StateException('O convite já foi persistido');
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
                throw new StateException('Já existe um convite para este usuário e grupo');
            }
            throw new PersistenceException('Erro de persistência no banco de dados.', 0, $e);
        }

        $id = (int) $this->pdo->lastInsertId();
        $invite->assignId($id);
    }

    public function update(Invite $invite) : void
    {
        if ($invite->getId() === null) {
            throw new StateException('O convite ainda não foi persistido');
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
            throw new PersistenceException('Erro de persistência no banco de dados.', 0, $e);
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
            throw new PersistenceException('Erro de persistência no banco de dados.', 0, $e);
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
            throw new PersistenceException('Erro de persistência no banco de dados.', 0, $e);
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
            throw new PersistenceException('Erro de persistência no banco de dados.', 0, $e);
        }

        $invites = [];
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $tuple) {
            $invites[] = $this->mapTuple($tuple);
        }

        return $invites;
    }

    public function findViewByInvitedUserId(int $invitedUserId) : array
    {
        $stmt = $this->pdo->prepare("
            SELECT i.*, g.name AS group_name, u.name AS user_name, u.email AS user_email
            FROM invites i
            INNER JOIN user_groups g ON g.id = i.group_id
            INNER JOIN users u ON u.id = i.invited_user_id
            WHERE i.invited_user_id = :invitedUserId
        ");

        $stmt->execute([':invitedUserId' => $invitedUserId]);

        return array_map(
            fn (array $tuple) => $this->mapViewTuple($tuple),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
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
            throw new PersistenceException('Erro de persistência no banco de dados.', 0, $e);
        }

        $invites = [];
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $tuple) {
            $invites[] = $this->mapTuple($tuple);
        }

        return $invites;
    }

    public function findViewByGroupId(int $groupId) : array
    {
        $stmt = $this->pdo->prepare("
            SELECT i.*, g.name AS group_name, u.name AS user_name, u.email AS user_email
            FROM invites i
            INNER JOIN user_groups g ON g.id = i.group_id
            INNER JOIN users u ON u.id = i.invited_user_id
            WHERE i.group_id = :groupId
        ");

        $stmt->execute([':groupId' => $groupId]);

        return array_map(
            fn (array $tuple) => $this->mapViewTuple($tuple),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
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
            throw new PersistenceException('Erro de persistência no banco de dados.', 0, $e);
        }

        $invites = [];
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $tuple) {
            $invites[] = $this->mapTuple($tuple);
        }

        return $invites;
    }

    public function findByInvitedUserIdAndGroupId(int $invitedUserId, int $groupId): ?Invite 
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM invites
            WHERE invited_user_id = :invited_user_id
            AND group_id = :group_id
            LIMIT 1
        ");

        $stmt->execute([
            ':invited_user_id' => $invitedUserId,
            ':group_id' => $groupId,
        ]);

        $tuple = $stmt->fetch(PDO::FETCH_ASSOC);

        return $tuple ? $this->mapTuple($tuple) : null;
    }

    public function expirePending(): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE invites
            SET status = 'expired'
            WHERE status = 'pending'
            AND expires_at <= CURRENT_TIMESTAMP
        ");

        $stmt->execute();
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

    private function mapViewTuple(array $tuple) : InviteView
    {
        return new InviteView(
            $this->mapTuple($tuple),
            $tuple['group_name'],
            $tuple['user_name'],
            $tuple['user_email']
        );
    }
}