<?php

namespace App\Services;

use LogicException;
use App\Models\Group\Group;
use App\Models\Group\GroupDAO;
use App\Models\Category\Category;
use App\Models\Category\CategoryDAO;

final class CategoryService
{
    public function __construct(
        private CategoryDAO $categoryDao,
        private GroupDAO $groupDao
    ) {}

    public function create(int $groupId, int $userId, string $name, string $color) : Category
    {
        $this->getGroupForAdmin($groupId, $userId);

        $category = Category::create($groupId, $name, $color);
        $this->categoryDao->insert($category);

        return $category;
    }

    public function rename(int $categoryId, int $groupId, int $userId, string $newName) : Category
    {
        $this->getGroupForAdmin($groupId, $userId);

        $category = $this->getCategoryForGroup($categoryId, $groupId);
        $category->changeName($newName);

        $this->categoryDao->update($category);
        return $category;
    }

    public function recolor(int $categoryId, int $groupId, int $userId, string $newColor) : Category
    {
        $this->getGroupForAdmin($groupId, $userId);

        $category = $this->getCategoryForGroup($categoryId, $groupId);
        $category->changeColor($newColor);

        $this->categoryDao->update($category);
        return $category;
    }

    public function findForGroup(int $groupId, int $userId) : array
    {
        $this->getGroupForMember($groupId, $userId);
        return $this->categoryDao->findByGroupId($groupId);
    }

    private function getGroup(int $groupId) : Group
    {
        $group = $this->groupDao->findById($groupId);

        if ($group === null) {
            throw new LogicException('Group not found');
        }

        return $group;
    }

    private function getGroupForMember(int $groupId, int $userId) : Group
    {
        $group = $this->getGroup($groupId);

        if (!$group->hasMember($userId)) {
            throw new LogicException('Only group members can perform this action');
        }

        return $group;
    }

    private function getGroupForAdmin(int $groupId, int $userId) : Group
    {
        $group = $this->getGroup($groupId);

        if (!$group->isAdmin($userId)) {
            throw new LogicException('Only group admins can perform this action');
        }

        return $group;
    }

    private function getCategory(int $categoryId) : Category
    {
        $category = $this->categoryDao->findById($categoryId);

        if ($category === null) {
            throw new LogicException('Category not found');
        }

        return $category;
    }

    private function getCategoryForGroup(int $categoryId, int $groupId) : Category
    {
        $category = $this->getCategory($categoryId);

        if ($category->getGroupId() !== $groupId) {
            throw new LogicException('Category not found');
        }

        return $category;
    }
}