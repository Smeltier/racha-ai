<?php

namespace App\Models\User;

use App\Exceptions\StateException;
use DateTimeImmutable;
use App\Exceptions\ValidationException;

final class User
{
    private ?int $id;
    private string $name;
    private Email $email;
    private string $passwordHash;
    private ?DateTimeImmutable $deletedAt;

    private function __construct(?int $id, string $name, Email $email, string $passwordHash, ?DateTimeImmutable $deletedAt = null)
    {
        $this->id = $id;
        $this->name = self::normalizeName($name);
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->deletedAt = $deletedAt;
    }

    public static function register(string $name, string $email, string $plainPassword): self
    {
        return new self(null, $name, Email::from($email), self::hashPassword($plainPassword));
    }

    public static function reconstitute(int $id, string $name, string $email, string $passwordHash, ?DateTimeImmutable $deletedAt = null): self
    {
        return new self($id, $name, Email::from($email), $passwordHash, $deletedAt);
    }

    public function verifyPassword(string $plainPassword): bool
    {
        if ($this->isDeleted()) {
            return false;
        }

        return password_verify($plainPassword, $this->passwordHash);
    }

    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new StateException('O usuário já possui um identificador');
        }

        $this->id = $id;
    }

    public function changeName(string $name): void
    {
        $this->assertNotDeleted();

        $this->name = self::normalizeName($name);
    }

    public function changeEmail(string $email): void
    {
        $this->assertNotDeleted();

        $this->email = Email::from($email);
    }

    public function changePassword(string $currentPassword, string $newPassword) : void
    {
        $this->assertNotDeleted();

        if (!$this->verifyPassword($currentPassword)) {
            throw new ValidationException("A senha atual não confere");
        }

        $this->passwordHash = self::hashPassword($newPassword);
    }

    public function delete(DateTimeImmutable $now) : void
    {
        if ($this->id === null) {
            throw new StateException("O usuário ainda não foi persistido");
        }

        $this->assertNotDeleted();

        $this->name = 'Deleted user';
        $this->email = Email::from("deleted_{$this->id}@invalid.example");
        $this->passwordHash = '';
        $this->deletedAt = $now;
    }

    public function isDeleted() : bool
    {
        return $this->deletedAt !== null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getDeletedAt() : ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    private static function normalizeName(string $name) : string
    {
        $name = trim($name);

        if ($name === '' || mb_strlen($name) > 150) {
            throw new ValidationException("O nome deve ter entre 1 e 150 caracteres");
        }

        return $name;
    }

    private static function hashPassword(string $plainPassword) : string
    {
        if (mb_strlen($plainPassword) < 8) {
            throw new ValidationException('A senha deve ter pelo menos 8 caracteres');
        }

        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    private function assertNotDeleted() : void
    {
        if ($this->isDeleted()) {
            throw new StateException("O usuário foi excluído");
        }
    }
}
