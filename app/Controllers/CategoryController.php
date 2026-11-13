<?php

namespace App\Controllers;

use LogicException;
use App\Http\Session;
use App\Services\CategoryService;

final class CategoryController extends BaseController
{
    public function __construct(
        private CategoryService $categoryService,
        Session $session
    ) {
        parent::__construct($session);
    }

    public function showCategories(int $groupId) : void
    {
        $userId = $this->requireLogin();

        $this->render('categories/index', [
            'categories' => $this->categoryService->findForGroup($groupId, $userId),
        ]);
    }

    public function create(int $groupId) : void
    {
        $userId = $this->requireLogin();
        $name = $this->input('name');
        $color = $this->input('color');

        try {
            $this->categoryService->create($groupId, $userId, $name, $color);
        } catch (InvalidArgumentException $e) {
            $this->renderCategories($groupId, $userId, $e->getMessage());
            return;
        }

        $this->redirect('/groups/' . $groupId . '/categories');
    }

    public function delete(int $groupId, int $categoryId) : void
    {
        $userId = $this->requireLogin();

        try {
            $this->categoryService->delete($categoryId, $groupId, $userId);
        } catch (InvalidArgumentException $e) {
            $this->renderCategories($groupId, $userId, $e->getMessage());
            return;
        }

        $this->redirect('/groups/' . $groupId . '/categories');
    }

    public function rename(int $groupId, int $categoryId) : void
    {
        $userId = $this->requireLogin();
        $newName = $this->input('name');

        try {
            $this->categoryService->rename($categoryId, $groupId, $userId, $newName);
        } catch (InvalidArgumentException $e) {
            $this->renderCategories($groupId, $userId, $e->getMessage());
            return;
        }

        $this->redirect('/groups/' . $groupId . '/categories');
    }

    public function recolor(int $groupId, int $categoryId) : void
    {
        $userId = $this->requireLogin();
        $newColor = $this->input('color');

        try {
            $this->categoryService->recolor($categoryId, $groupId, $userId, $newColor);
        } catch (InvalidArgumentException $e) {
            $this->renderCategories($groupId, $userId, $e->getMessage());
            return;
        }

        $this->redirect('/groups/' . $groupId . '/categories');
    }

    private function renderCategories(int $groupId, int $userId, ?string $errorMessage = null) : void
    {
        $this->render('categories/index', [
            'categories' => $this->categoryService->findForGroup($groupId, $userId),
            'errorMessage' => $errorMessage,
        ]);
    }
}