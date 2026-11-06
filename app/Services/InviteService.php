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
        private InviteDAO $inviteDao,
        private GroupDAO $groupDao,
        private UserDAO $userDao
    ) {}

    public function create(int $inviterUserId, int $invitedUserId, int $groupId) : Invite
    {
        $group = $this->groupDao->findById($groupId);

        if ($group === null) {
            throw new LogicException('Group not found');
        }

        if ($group->isAdmin($inviterUserId)) {
            throw new LogicException('Only group admins can invite users');
        }

        if ($this->userDao->findById($invitedUserId) === null) {
            throw new LogicException('User not found');
        }

        if ($group->hasMember($invitedUserId)) {
            throw new LogicException('User is already a member of the group');
        }
    
        $invite = Invite::create($invitedUserId, $groupId);
        $this->inviteDao->insert($invite);

        return $invite;
    }

    public function delete(int $id) : void
    {
        $this->inviteDao->delete($id);
    }

    public function accept(int $id) : void
    {
        $invite = $this->getInvite($id);
        $invite->accept();
        $this->inviteDao->update($invite);
    }

    public function decline(int $id) : void
    {
        $invite = $this->getInvite($id);
        $invite->decline();
        $this->inviteDao->update($invite);
    }

    public function findById(int $id) : Invite
    {
        return $this->getInvite($id);
    }

    public function findByInvitedUserId(int $invitedUserId) : array
    {
        return $this->inviteDao->findByInvitedUserId($invitedUserId);
    }

    public function findByInvitedUserIdAndInviteStatus(int $invitedUserId, InviteStatus $status) : array
    {
        return $this->inviteDao->findByInvitedUserIdAndInviteStatus($invitedUserId, $status);
    }

    public function findByGroupId(int $groupId) : array
    {
        return $this->inviteDao->findByGroupId($groupId);
    }

    private function getInvite(int $id) : Invite
    {
        $invite = $this->inviteDao->findById($id);

        if ($invite === null) {
            throw new LogicException('Invite not found');
        }

        return $invite;
    }
}