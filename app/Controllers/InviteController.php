<?php

namespace App\Controllers;

use LogicException;
use App\Http\Session;
use App\Services\InviteService;

final class InviteController extends BaseController
{
    public function __construct(
        private InviteService $inviteService,
        private Session $session
    ) {
        parent::__construct($session);
    }

    public function showInvites() : void
    {
        $userId = $this->requireLogin();

        $invites = $this->inviteService->findAllForUser($userId);

        $this->render('invites/index', [
            'invites' => $invites,
        ]);
    }

    public function showGroupInvites(int $groupId) : void
    {
        $userId = $this->requireLogin();

        try {
            $invites = $this->inviteService->findByGroupId($groupId, $userId);
        } catch (LogicException) {
            http_response_code(404);
            echo 'Group not found';
            return;
        }

        $this->render('invites/group', [
            'invites' => $invites,
            'groupId' => $groupId,
        ]);
    }

    public function invite(int $groupId) : void
    {
    }

    public function accept(int $inviteId) : void
    {
    }

    public function decline(int $inviteId) : void
    {
    }

    public function cancel(int $inviteId) : void
    {
    }
}