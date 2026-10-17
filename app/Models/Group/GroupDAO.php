<?php

namespace App\Models\Group;

use LogicException;
use PDO;
use PDOException;

final class GroupDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert() : int
    {
        return 1;
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

    private function insertGroup(Group $group) : int
    {
        if ($group->getId() !== null) {
            throw new LogicException('Group already persisted');
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO user_groups
             (name)
             VALUES (:name)"
        );

        try {
            $stmt->execute(['name' => $group->getName()]);
        } catch (PDOException $e) {
            throw $e;
        }

        $id = (int) $this->pdo->lastInsertId();
        $group->assignId($id);
        return $id;
    }

    private function insertMember(int $groupId, GroupMember $member) : void
    {

    }

    private function deleteMember(int $groupId, int $memberId) : void
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
