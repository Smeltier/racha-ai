<?php

namespace App\Controllers;

use LogicException;
use App\Http\Session;
use App\Services\InviteService;

final class InviteController extends BaseController
{
    public function __construct(
        private InviteService $inviteService,
        Session $session
    ) {
        parent::__construct($session);
    }

    public function showInvites() : void
    {
        $userId = $this->requireLogin();

        $this->render('invites/index', [
            'invites' => $this->inviteService->findAllForUserView($userId),
        ]);
    }

    public function showGroupInvites(int $groupId) : void
    {
        $userId = $this->requireLogin();
        $this->renderGroupInvites($groupId, $userId);
    }

    public function invite(int $groupId) : void
    {
        $userId = $this->requireLogin();
        $email = $this->input('email');

        try {
            $this->inviteService->createForEmail($userId, $email, $groupId);
        } catch (LogicException $e) {
            $this->renderGroupInvites($groupId, $userId, $e->getMessage());
            return;
        }

        $this->redirect('/groups/' . $groupId . '/invites');
    }

    public function accept(int $inviteId) : void
    {
        $userId = $this->requireLogin();

        try {
            $this->inviteService->accept($inviteId, $userId);
        } catch (LogicException $e) {
            $this->renderUserInvites($userId, $e->getMessage());
            return;
        }

        $this->redirect('/invites');
    }

    public function decline(int $inviteId) : void
    {
        $userId = $this->requireLogin();

        try {
            $this->inviteService->decline($inviteId, $userId);
        } catch (LogicException $e) {
            $this->renderUserInvites($userId, $e->getMessage());
            return;
        }

        $this->redirect('/invites');
    }

    public function cancel(int $inviteId) : void
    {
        $userId = $this->requireLogin();

        try {
            $invite = $this->inviteService->findForGroupAdmin($inviteId, $userId);
            $this->inviteService->cancel($inviteId, $userId);
        } catch (LogicException $e) {
            http_response_code(404);
            echo 'Invite not found';
            return;
        }

        $this->redirect('/groups/' . $invite->getGroupId() . '/invites');
    }

    private function renderGroupInvites(int $groupId, int $userId, ?string $error = null) : void
    {
        try {
            $invites = $this->inviteService->findByGroupIdView($groupId, $userId);
        } catch (LogicException) {
            http_response_code(404);
            echo 'Group not found';
            return;
        }

        $this->render('invites/group', [
            'invites' => $invites,
            'groupId' => $groupId,
            'error' => $error,
        ]);
    }

    private function renderUserInvites(int $userId, ?string $error = null) : void
    {
        $invites = $this->inviteService->findAllForUserView($userId);

        $this->render('invites/index', [
            'invites' => $invites,
            'error' => $error,
        ]);
    }
}