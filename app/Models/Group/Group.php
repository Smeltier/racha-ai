<?php

namespace App\Models\Group;

use App\Exceptions\StateException;
use DateTimeImmutable;
use App\Exceptions\ValidationException;

final class Group
{
    private const MAX_NAME_LENGTH = 200;

    private ?int $id;
    private string $name;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $deletedAt;
    private array $members = [];

    private function __construct(?int $id, string $name, DateTimeImmutable $createdAt, ?DateTimeImmutable $deletedAt)
    {
        $this->id = $id;
        $this->name = self::validateName($name);
        $this->createdAt = $createdAt;
        $this->deletedAt = $deletedAt;
    }

    public static function create(string $name, int $creatorId) : self
    {
        $now = new DateTimeImmutable();
        $group = new self(null, $name, $now, null);
        $group->members[$creatorId] = new GroupMember($creatorId, GroupRole::Admin, $now);
        return $group;
    }

    public static function reconstitute(int $id, string $name, DateTimeImmutable $createdAt, ?DateTimeImmutable $deletedAt, array $members) : self
    {
        $group = new self($id, $name, $createdAt, $deletedAt);
        foreach ($members as $member) {
            $group->members[$member->userId] = $member;
        }
        return $group;
    }

    public function assignId(int $id) : void
    {
        if ($this->id !== null) {
            throw new StateException("O grupo já possui um identificador");
        }

        $this->id = $id;
    }

    public function rename(string $name) : void
    {
        $this->assertNotDeleted();
        $this->name = self::validateName($name);
    }

    public function addMember(int $userId) : void
    {
        $this->assertNotDeleted();

        if ($this->hasMember($userId)) {
            throw new ValidationException('O usuário já é membro deste grupo.');
        }

        $now = new DateTimeImmutable();
        $this->members[$userId] = new GroupMember($userId, GroupRole::Member, $now);
    }

    public function removeMember(int $userId) : void
    {
        $this->assertNotDeleted();

        $member = $this->requireMember($userId);
        if ($member->isAdmin() && $this->adminCount() === 1) {
            throw new ValidationException('O último administrador não pode ser removido.');
        }
        unset($this->members[$userId]);
    }

    public function leave(int $userId) : void
    {
        $this->assertNotDeleted();
        $member = $this->requireMember($userId);

        if ($member->isAdmin() && $this->adminCount() === 1 && $this->memberCount() > 1) {
            throw new ValidationException('O último administrador não pode sair do grupo.');
        }

        unset($this->members[$userId]);

        if ($this->memberCount() === 0) {
            $this->delete();
            return;
        }
    }

    public function promote(int $userId) : void
    {
        $this->assertNotDeleted();
        $this->members[$userId] = $this->requireMember($userId)->withRole(GroupRole::Admin);
    }

    public function demote(int $userId) : void
    {
        $this->assertNotDeleted();

        $member = $this->requireMember($userId);
        if ($member->isAdmin() && $this->adminCount() === 1) {
            throw new ValidationException('O último administrador não pode ser rebaixado.');
        }

        $this->members[$userId] = $member->withRole(GroupRole::Member);
    }

    public function hasMember(int $userId) : bool
    {
        return isset($this->members[$userId]);
    }

    public function isAdmin(int $userId) : bool
    {
        return $this->hasMember($userId) && $this->members[$userId]->isAdmin();
    }

    public function memberCount() : int
    {
        return count($this->members);
    }

    public function delete() : void
    {
        $this->assertNotDeleted();
        $this->deletedAt = new DateTimeImmutable();
    }

    public function isDeleted() : bool
    {
        return $this->deletedAt !== null;
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getCreatedAt() : DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getDeletedAt() : ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function getMembers() : array
    {
        return array_values($this->members);
    }

    private function requireMember(int $userId) : GroupMember
    {
        return $this->members[$userId] ?? throw new ValidationException('O usuário não é membro deste grupo.');
    }

    private function adminCount() : int
    {
        return count(array_filter($this->members, fn (GroupMember $m) => $m->isAdmin()));
    }

    private static function validateName(string $name) : string
    {
        $name = trim($name);

        if ($name === '' || mb_strlen($name) > self::MAX_NAME_LENGTH) {
            throw new ValidationException('O nome do grupo deve ter entre 1 e 200 caracteres.');
        }

        return $name;
    }

    private function assertNotDeleted() : void
    {
        if ($this->isDeleted()) {
            throw new StateException("O usuário foi excluído");
        }
    }
}
