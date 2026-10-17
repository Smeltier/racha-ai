<?php

namespace App\Models\Group;

use LogicException;
use PDO;
use PDOException;
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

    public function update() : void
    {

    }

    public function findById(int $id) : ?Group
    {
        return null;
    }

    public function findByUserId(int $userId) : array
    {
        return [];
    }

    private function insertGroup(Group $group)
    {
        if ($group->getId() !== null) {
            throw new LogicException('Group already persisted');
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO user_groups
             (name, created_at)
             VALUES (:name, :created_at)"
        );

        $stmt->execute([
            ':name' => $group->getName(),
            ':created_at' => $group->getCreatedAt()->format('Y-m-d H:i:s')
        ]);

        $group->assignId((int) $this->pdo->lastInsertId());
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

    private function deleteMember(int $groupId, int $userId) : void
    {

    }

    private function updateRole(int $groupId, GroupMember $member) : void
    {

    }

    private function findByGroupId(int $groupId) : array
    {
        return [];
    }
}
