<?php

namespace App\Controllers;

use LogicException;
use App\Http\Session;
use App\Services\GroupService;
use App\Exceptions\ValidationException;
use App\Exceptions\GroupNotFoundException;

final class GroupController extends BaseController
{
    public function __construct(
        private GroupService $groupService,
        Session $session
    ) {
        parent::__construct($session);
    }

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

    public function showEdit(int $id) : void
    {
        $userId = $this->requireLogin();

        try {
            $group = $this->groupService->findByIdForUser($id, $userId);
            if (!$group->isAdmin($userId)) {
                throw new GroupNotFoundException('Group not found');
            }
        } catch (GroupNotFoundException) {
            http_response_code(404);
            echo 'Group not found';
            return;
        }

        $this->render('groups/edit', [
            'group' => $group,
        ]);
    }

    public function update(int $id) : void
    {
        $userId = $this->requireLogin();
        $name = $this->input('name');

        try {
            $this->groupService->changeNameForAdmin($id, $userId, $name);
        } catch (GroupNotFoundException) {
            http_response_code(404);
            echo 'Group not found';
            return;
        } catch (ValidationException $e) {
            $this->renderEditError($id, $userId, $name, $e->getMessage());
            return;
        } catch (LogicException $e) {
            $this->renderEditError($id, $userId, $name, $e->getMessage());
            return;
        }

        $this->redirect('/groups/' . $id);
    }

    public function delete(int $id) : void
    {
        $userId = $this->requireLogin();

        try {
            $this->groupService->deleteForAdmin($id, $userId);
        } catch (GroupNotFoundException) {
            http_response_code(404);
            echo 'Group not found';
            return;
        }

        $this->redirect('/groups');
    }

    public function removeMember(int $groupId, int $memberId) : void
    {
        $this->updateMemberRole($groupId, $memberId, 'remove');
    }

    public function promoteMember(int $groupId, int $memberId) : void
    {
        $this->updateMemberRole($groupId, $memberId, 'promote');
    }

    public function demoteMember(int $groupId, int $memberId) : void
    {
        $this->updateMemberRole($groupId, $memberId, 'demote');
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

        $this->render('groups/show', [
            'group' => $group,
            'userId' => $userId,
        ]);
    }

    private function updateMemberRole(int $groupId, int $memberId, string $action) : void
    {
        $adminId = $this->requireLogin();

        try {
            match ($action) {
                'remove' => $this->groupService->removeMember($groupId, $adminId, $memberId),
                'promote' => $this->groupService->promoteMemberRole($groupId, $adminId, $memberId),
                'demote' => $this->groupService->demoteMemberRole($groupId, $adminId, $memberId),
            };
        } catch (GroupNotFoundException) {
            http_response_code(404);
            echo 'Group not found';
            return;
        } catch (ValidationException $e) {
            try {
                $group = $this->groupService->findByIdForUser($groupId, $adminId);
            } catch (GroupNotFoundException) {
                http_response_code(404);
                echo 'Group not found';
                return;
            }

            $this->render('groups/show', [
                'group' => $group,
                'userId' => $adminId,
                'error' => $e->getMessage(),
            ]);
            return;
        } catch (LogicException) {
            http_response_code(404);
            echo 'Group not found';
            return;
        }

        $this->redirect('/groups/' . $groupId);
    }

    private function renderEditError(int $id, int $userId, string $name, string $error) : void
    {
        try {
            $group = $this->groupService->findByIdForUser($id, $userId);
        } catch (GroupNotFoundException) {
            http_response_code(404);
            echo 'Group not found';
            return;
        }

        $this->render('groups/edit', [
            'group' => $group,
            'error' => $error,
            'name' => $name,
        ]);
    }
}
