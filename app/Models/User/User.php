<?php

namespace App\Models\User;

use InvalidArgumentException;
use LogicException;

final class User
{
    private ?int $id;
    private string $name;
    private string $email;
    private string $passwordHash;

    private function __construct(?int $id, string $name, string $email, string $passwordHash)
    {
        $this->id = $id;
        $this->changeName($name);
        $this->changeEmail($email);
        $this->passwordHash = $passwordHash;
    }

    public static function register(string $name, string $email, string $plainPassword): self
    {
        if (mb_strlen($plainPassword) < 8) {
            throw new InvalidArgumentException('Password must have at least 8 characters');
        }

        return new self(null, $name, $email, password_hash($plainPassword, PASSWORD_ARGON2ID));
    }

    public static function reconstitute(int $id, string $name, string $email, string $passwordHash): self
    {
        return new self($id, $name, $email, $passwordHash);
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->passwordHash);
    }

    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new LogicException('User already has an id');
        }

        $this->id = $id;
    }

    public function changeName(string $name): void
    {
        $name = trim($name);

        if ($name === '' || mb_strlen($name) > 150) {
            throw new InvalidArgumentException('Name must have between 1 and 150 characters');
        }

        $this->name = $name;
    }

    public function changeEmail(string $email): void
    {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 320) {
            throw new InvalidArgumentException('Invalid email');
        }

        $this->email = $email;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }
}
