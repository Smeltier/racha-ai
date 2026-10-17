<?php

namespace App\Models\Group;

use DateTimeImmutable;
use LogicException;
use PDO;
use Throwable;

final class GroupDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(Group $group) : void
    {
        if ($group->getId() !== null) {
            throw new LogicException('Group already persisted');
        }

        $ownsTransaction = !$this->pdo->inTransaction();

        if ($ownsTransaction) {
            $this->pdo->beginTransaction();
        }

        try {
            $this->insertGroup($group);

            foreach ($group->getMembers() as $member) {
                $this->insertMember($group->getId(), $member);
            }

            if ($ownsTransaction) {
                $this->pdo->commit();
            }
        } catch (Throwable $e) {
            if ($ownsTransaction) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function update(Group $group) : void
    {
        if ($group->getId() === null) {
            throw new LogicException('Group not persisted');
        }

        $ownsTransaction = !$this->pdo->inTransaction();

        if ($ownsTransaction) {
            $this->pdo->beginTransaction();
        }

        try {
            $this->updateGroup($group);
            $this->syncMembers($group);

            if ($ownsTransaction) {
                $this->pdo->commit();
            }
        } catch (Throwable $e) {
            if ($ownsTransaction) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function findById(int $id) : ?Group
    {
        $stmt = $this->pdo->prepare("
            SELECT id, name, created_at
            FROM user_groups
            WHERE id = :id AND deleted_at IS NULL
        ");

        $stmt->execute(['id' => $id]);
        $tuple = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($tuple === false) {
            return null;
        }

        return $this->mapGroupTuple($tuple);
    }

    public function findByUserId(int $userId) : array
    {
        $stmt = $this->pdo->prepare("
            SELECT g.id, g.name, g.created_at
            FROM user_groups g
            INNER JOIN group_members gm
            ON gm.group_id = g.id
            WHERE gm.user_id = :user_id and g.deleted_at IS NULL
            ORDER BY g.name
        ");

        $stmt->execute([':user_id' => $userId]);

        $groups = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $tuple) {
            $groups[] = $this->mapGroupTuple($tuple);
        }

        return $groups;
    }

    private function insertGroup(Group $group) : void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO user_groups
            (name, created_at)
            VALUES (:name, :created_at)
        ");

        $stmt->execute([
            ':name' => $group->getName(),
            ':created_at' => $group->getCreatedAt()->format('Y-m-d H:i:s')
        ]);

        $group->assignId((int) $this->pdo->lastInsertId());
    }

    private function updateGroup(Group $group) : void
    {
        $stmt = $this->pdo->prepare("
            UPDATE user_groups
            SET name = :name, deleted_at = :deleted_at
            WHERE id = :id AND deleted_at IS NULL
        ");

        $stmt->execute([
            ':id' => $group->getId(),
            ':name' => $group->getName(),
            ':deleted_at' => $group->getDeletedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    private function insertMember(int $groupId, GroupMember $member) : void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO group_members
             (group_id, user_id, role, joined_at)
             VALUES (:group_id, :user_id, :role, :joined_at)"
        );

        $stmt->execute([
            ':group_id' => $groupId,
            ':user_id' => $member->userId,
            ':role' => $member->role->value,
            ':joined_at' => $member->joinedAt->format('Y-m-d H:i:s')
        ]);
    }

    private function syncMembers(Group $group) : void
    {
        $groupId = $group->getId();
        $currentMembers = $this->findByGroupId($groupId);

        $currentMap = [];
        foreach ($currentMembers as $member) {
            $currentMap[$member->userId] = $member;
        }

        $newMap = [];
        foreach ($group->getMembers() as $member) {
            $newMap[$member->userId] = $member;
        }

        foreach ($currentMap as $userId => $member) {
            if(!isset($newMap[$userId])) {
                $this->deleteMember($groupId, $userId);
            }
        }

        foreach ($newMap as $userId => $member) {
            if (!isset($currentMap[$userId])) {
                $this->insertMember($groupId, $member);
            } elseif ($currentMap[$userId]->role !== $member->role) {
                $this->updateRole($groupId, $member);
            }
        }
    }

    private function deleteMember(int $groupId, int $userId) : void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM group_members
            WHERE group_id = :group_id AND user_id = :user_id
        ");

        $stmt->execute([
            ':group_id' => $groupId,
            ':user_id' => $userId
        ]);
    }

    private function updateRole(int $groupId, GroupMember $member) : void
    {
        $stmt = $this->pdo->prepare("
            UPDATE group_members
            SET role = :role
            WHERE group_id = :group_id AND user_id = :user_id
        ");

        $stmt->execute([
            ':group_id' => $groupId,
            ':user_id' => $member->userId,
            ':role' => $member->role->value
        ]);
    }

    private function findByGroupId(int $groupId) : array
    {
        $stmt = $this->pdo->prepare("
            SELECT user_id, role, joined_at
            FROM group_members
            WHERE group_id = :group_id
        ");

        $stmt->execute([':group_id' => $groupId]);

        $members = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $tuple) {
            $members[] = $this->mapMemberTuple($tuple);
        }

        return $members;
    }

    private function mapGroupTuple(array $tuple) : Group
    {
        $id = (int) $tuple['id'];
        return Group::reconstitute(
            $id,
            $tuple['name'],
            new DateTimeImmutable($tuple['created_at']),
            null,
            $this->findByGroupId($id)
        );
    }

    private function mapMemberTuple(array $tuple) : GroupMember
    {
        return new GroupMember(
            (int) $tuple['user_id'],
            GroupRole::from($tuple['role']),
            new DateTimeImmutable($tuple['joined_at'])
        );
    }
}
