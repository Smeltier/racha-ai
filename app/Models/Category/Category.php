<?php

namespace App\Models\Category;

use InvalidArgumentException;

final class Category
{
    private function __construct(
        private ?int $id,
        private int $groupId,
        private string $name,
        private string $color
    ) {}

    public function __toString(): string
    {
        return '#' . $this->color;
    }

    public static function create(?int $id, int $groupId, string $name, ?string $color): self
    {
        if (is_null($color)) {
            $color = 'FFFFFF';
        }

        if (!preg_match('/^[A-Fa-f0-9]{6}$/', $color)) {
            throw new InvalidArgumentException('Color must be a valid hex color code.');
        }

        return new self($id, $groupId, $name, $color);
    }

    public static function reconstitute(int $id, int $groupId, string $name, string $color): self
    {
        return new self($id, $groupId, $name, $color);
    }

    public function assignId(int $id): void
    {
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