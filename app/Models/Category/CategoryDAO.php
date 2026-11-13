<?php

namespace App\Models\Category;

use LogicException;
use PDO;
use PDOException;

final class CategoryDAO
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function insert(Category $category) : void
    {
        if ($category->getId() !== null) {
            throw new LogicException('Category already persisted');
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO categories (group_id, name, color)
            VALUES (:group_id, :name, :color)
        ");

        $stmt->bindValue(':group_id', $category->getGroupId(), PDO::PARAM_INT);
        $stmt->bindValue(':name', $category->getName(), PDO::PARAM_STR);
        $stmt->bindValue(':color', $category->getColor(), PDO::PARAM_STR);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new LogicException('Category already exists for this group');
            }

            throw $e;
        }

        $id = (int) $this->pdo->lastInsertId();
        $category->assignId($id);
    }

    public function update(Category $category) : void
    {
        if ($category->getId() === null) {
            throw new LogicException('Category not persisted');
        }

        $stmt = $this->pdo->prepare("
            UPDATE categories
            SET group_id = :group_id, name = :name, color = :color
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $category->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':group_id', $category->getGroupId(), PDO::PARAM_INT);
        $stmt->bindValue(':name', $category->getName(), PDO::PARAM_STR);
        $stmt->bindValue(':color', $category->getColor(), PDO::PARAM_STR);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function delete(Category $category) : void
    {
        if ($category->getId() === null) {
            throw new LogicException('Category not persisted');
        }

        $stmt = $this->pdo->prepare("
            DELETE FROM categories
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $category->getId(), PDO::PARAM_INT);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function findById(int $id) : ?Category
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM categories
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }

        $tuple = $stmt->fetch(PDO::FETCH_ASSOC);
        return $tuple ? $this->mapTuple($tuple) : null;
    }

    public function findByGroupId(int $groupId) : array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM categories
            WHERE group_id = :groupId
        ");

        $stmt->bindValue(':groupId', $groupId, PDO::PARAM_INT);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }

        $categories = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $tuple) {
            $categories[] = $this->mapTuple($tuple);
        }

        return $categories;
    }

    private function mapTuple(array $tuple) : Category
    {
        return Category::reconstitute(
            (int)$tuple['id'],
            (int)$tuple['group_id'],
            $tuple['name'],
            $tuple['color']
        );
    }
}