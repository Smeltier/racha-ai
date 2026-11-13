<?php

namespace App\Services;

use App\Exceptions\StateException;
use App\Models\Group\Group;
use App\Models\Group\GroupDAO;
use App\Models\User\UserDAO;
use App\Exceptions\GroupNotFoundException;

final class GroupService
{
    public function __construct(
        private GroupDAO $dao,
        private UserDAO $userDao
    ) {}

    public function create(int $userId, string $name) : Group
    {
        $group = Group::create($name, $userId);
        $this->dao->insert($group);

        return $group;
    }

    public function delete(int $id) : void
    {
        $group = $this->getGroup($id);
        $group->delete();
        $this->dao->update($group);
    }

    public function deleteForAdmin(int $id, int $adminId) : void
    {
        $group = $this->getGroupForAdmin($id, $adminId);
        $group->delete();
        $this->dao->update($group);
    }

    public function changeName(int $id, string $name) : Group
    {
        $group = $this->getGroup($id);
        $group->rename($name);
        $this->dao->update($group);

        return $group;
    }

    public function changeNameForAdmin(int $id, int $adminId, string $name) : Group
    {
        $group = $this->getGroupForAdmin($id, $adminId);
        $group->rename($name);
        $this->dao->update($group);

        return $group;
    }

    public function findById(int $id) : Group
    {
        return $this->getGroup($id);
    }

    public function findByIdForUser(int $id, int $userId) : Group
    {
        $group = $this->getGroup($id);

        if (!$group->hasMember($userId)) {
            throw new GroupNotFoundException('Grupo não encontrado');
        }

        return $group;
    }

    public function findByIdViewForUser(int $id, int $userId) : GroupView
    {
        $group = $this->findByIdForUser($id, $userId);
        $members = [];

        foreach ($group->getMembers() as $member) {
            $user = $this->userDao->findById($member->userId);

            if ($user === null) {
                throw new GroupNotFoundException('Grupo não encontrado');
            }

            $members[] = new GroupMemberView($member, $user->getName());
        }

        return new GroupView($group, $members);
    }

    public function findByUserId(int $userId) : array
    {
        $groups = $this->dao->findByUserId($userId);

        if (empty($groups)) {
            throw new GroupNotFoundException('Grupos não encontrados');
        }

        return $groups;
    }

    public function addMembers(int $id, array $members) : Group
    {
        if (empty($members)) {
            throw new StateException('A lista de membros não pode estar vazia');
        }

        $group = $this->getGroup($id);
        foreach ($members as $member) {
            $group->addMember($member);
        }

        $this->dao->update($group);

        return $group;
    }

    public function removeMember(int $id, int $adminId, int $memberId) : Group
    {
        $group = $this->getGroupForAdmin($id, $adminId);
        $group->removeMember($memberId);

        $this->dao->update($group);

        return $group;
    }

    public function leave(int $id, int $userId) : void
    {
        $group = $this->getGroup($id);

        if (!$group->hasMember($userId)) {
            throw new GroupNotFoundException('Grupo não encontrado');
        }

        $group->leave($userId);
        $this->dao->update($group);
    }

    public function promoteMemberRole(int $id, int $adminId, int $memberId) : Group
    {
        $group = $this->getGroupForAdmin($id, $adminId);
        $group->promote($memberId);
        $this->dao->update($group);
        return $group;
    }

    public function demoteMemberRole(int $id, int $adminId, int $memberId) : Group
    {
        $group = $this->getGroupForAdmin($id, $adminId);
        $group->demote($memberId);
        $this->dao->update($group);
        return $group;
    }

    private function getGroupForAdmin(int $id, int $adminId) : Group
    {
        $group = $this->getGroup($id);

        if (!$group->isAdmin($adminId)) {
            throw new GroupNotFoundException('Grupo não encontrado');
        }

        return $group;
    }

    private function getGroup(int $id) : Group
    {
        $group = $this->dao->findById($id);

        if ($group === null) {
            throw new GroupNotFoundException('Grupo não encontrado');
        }

        return $group;
    }
}