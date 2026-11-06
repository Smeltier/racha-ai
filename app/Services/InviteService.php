<?php

namespace App\Services;

use LogicException;
use App\Models\Invite\Invite;
use App\Models\Invite\InviteDAO;
use App\Models\Invite\InviteStatus;
use App\Models\Group\GroupDAO;
use App\Models\User\UserDAO;

final class InviteService
{
    public function __construct(
        private InviteDAO $dao,
        private GroupDAO $groupDao,
        private UserDAO $userDao
    ) {}

    public function create(int $invitedUserId, int $groupId) : Invite
    {
        $invite = Invite::create($invitedUserId, $groupId);
        $this->dao->insert($invite);

        return $invite;
    }

    public function delete(int $id) : void
    {
        $this->dao->delete($id);
    }

    public function accept(int $id) : void
    {
        $invite = $this->getInvite($id);
        $invite->accept();
        $this->dao->update($invite);
    }

    public function decline(int $id) : void
    {
        $invite = $this->getInvite($id);
        $invite->decline();
        $this->dao->update($invite);
    }

    public function findById(int $id) : Invite
    {
        return $this->getInvite($id);
    }

    public function findByInvitedUserId(int $invitedUserId) : array
    {
        return $this->dao->findByInvitedUserId($invitedUserId);
    }

    public function findByInvitedUserIdAndInviteStatus(int $invitedUserId, InviteStatus $status) : array
    {
        return $this->dao->findByInvitedUserIdAndInviteStatus($invitedUserId, $status);
    }

    public function findByGroupId(int $groupId) : array
    {
        return $this->dao->findByGroupId($groupId);
    }

    private function getInvite(int $id) : Invite
    {
        $invite = $this->dao->findById($id);

        if ($invite === null) {
            throw new LogicException('Invite not found');
        }

        return $invite;
    }
}