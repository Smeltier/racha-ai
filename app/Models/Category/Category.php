<?php

namespace App\Models\Category;

use App\Exceptions\ValidationException;

final class Category
{
    private ?int $id;
    private string $color;
    private int $groupId;
    private string $name;

    private function __construct(?int $id, int $groupId, string $name, string $color) {
        $this->id = $id;
        $this->groupId = $groupId;
        $this->changeName($name);
        $this->changeColor($color);
    }

    public function __toString(): string
    {
        return '#' . $this->color;
    }

    public static function create(int $groupId, string $name, string $color): self
    {
        return new self(null, $groupId, $name, $color);
    }

    public static function reconstitute(int $id, int $groupId, string $name, string $color): self
    {
        return new self($id, $groupId, $name, $color);
    }

    public function changeName(string $name): void
    {
        $name = trim($name);

        if ($name === '') {
            throw new ValidationException('O nome não pode ficar vazio.');
        }

        if (mb_strlen($name) > 50) {
            throw new ValidationException('O nome deve ter no máximo 50 caracteres.');
        }

        $this->name = $name;
    }

    public function changeColor(string $color): void
    {
        $color = trim($color);

        if ($color === '') {
            throw new ValidationException('A cor não pode ficar vazia.');
        }

        if (!preg_match('/^[A-Fa-f0-9]{6}$/', $color)) {
            throw new ValidationException('A cor deve ser um código hexadecimal válido.');
        }

        $this->color = $color;
    }

    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new ValidationException('O identificador já foi atribuído.');
        }

        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColor(): string
    {
        return $this->color;
    }
}
