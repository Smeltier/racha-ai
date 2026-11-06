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

    public function delete(int $inviteId) : void
    {
        $this->inviteDao->delete($inviteId);
    }

    public function accept(int $inviteId, int $userId) : void
    {
        $invite = $this->getInviteForUser($inviteId, $userId);
        $invite->accept();
        $this->inviteDao->update($invite);
    }

    public function decline(int $inviteId, int $userId) : void
    {
        $invite = $this->getInviteForUser($inviteId, $userId);
        $invite->decline();
        $this->inviteDao->update($invite);
    }

    public function findById(int $inviteId) : Invite
    {
        return $this->getInvite($inviteId);
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

    private function getInvite(int $inviteId) : Invite
    {
        $invite = $this->inviteDao->findById($inviteId);

        if ($invite === null) {
            throw new LogicException('Invite not found');
        }

        return $invite;
    }

    private function getInviteForUser(int $inviteId, int $userId) : Invite
    {
        $invite = $this->getInvite($inviteId);

        if ($invite->getInvitedUserId() !== $userId) {
            throw new LogicException('User is not the invited user for this invite');
        }

        return $invite;
    }
}