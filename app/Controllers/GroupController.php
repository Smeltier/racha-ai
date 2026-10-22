<?php

namespace App\Controllers;

use App\Http\Session;
use App\Services\GroupService;
use App\Exceptions\ValidationException;
use App\Exceptions\GroupNotFoundException;

final class GroupController
{
    public function __construct(
        private GroupService $groupService,
        private Session $session
    ) {}

    public function create() : void
    {
        $userId = $this->requireLogin();
        $name = $this->input('name');

        try {
            $this->groupService->create($userId, $name);
        } catch (ValidationException $e) {
            $this->render('groups/create', [
                'error' => $e->getMessage(),
                'name' => $name,
            ]);
            return;
        }

        $this->redirect('/groups');
    }

    public function showCreate() : void
    {
        $this->requireLogin();
        $this->render('groups/create');
    }

    public function showGroups() : void
    {
        $userId = $this->requireLogin();

        try {
            $groups = $this->groupService->findByUserId($userId);
        } catch (GroupNotFoundException) {
            $groups = [];
        }

        $this->render('groups/index', ['groups' => $groups]);
    }

    public function show(int $id) : void
    {
        $userId = $this->requireLogin();

        try {
            $group = $this->groupService->findByIdForUser($id, $userId);
        } catch (GroupNotFoundException) {
            http_response_code(404);
            echo 'Group not found';
            return;
        }

        $this->render('groups/show', ['group' => $group]);
    }

    private function redirect(string $path): never
    {
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        header('Location: ' . $base . $path);
        exit;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.php';
    }

    private function input(string $key): string
    {
        $value = $_POST[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    private function requireLogin() : int
    {
        $userId = $this->session->userId();

        if ($userId === null) {
            $this->redirect('/login');
        }

        return $userId;
    }
}
