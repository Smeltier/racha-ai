<?php

namespace App\Services;

use LogicException;
use Throwable;
use PDO;
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
        private UserDAO $userDao,
        private PDO $pdo
    ) {}

    public function create(int $inviterUserId, int $invitedUserId, int $groupId) : Invite
    {
        if ($inviterUserId === $invitedUserId) {
            throw new LogicException('Cannot invite yourself to a group');
        }

        $group = $this->groupDao->findById($groupId);

        if ($group === null) {
            throw new LogicException('Group not found');
        }

        if (!$group->isAdmin($inviterUserId)) {
            throw new LogicException('Only group admins can invite users');
        }

        if ($this->userDao->findById($invitedUserId) === null) {
            throw new LogicException('User not found');
        }

        if ($group->hasMember($invitedUserId)) {
            throw new LogicException('User is already a member of the group');
        }

        $ownsTransaction = !$this->pdo->inTransaction();

        if ($ownsTransaction) {
            $this->pdo->beginTransaction();
        }

        try {
            $existingInvite = $this->inviteDao->findByInvitedUserIdAndGroupId($invitedUserId, $groupId);
    
            if ($existingInvite !== null) {
                if ($existingInvite->isPending()) {
                    throw new LogicException('An invite is already pending for this user and group');
                }
    
                $this->inviteDao->delete($existingInvite->getId());
            }
        
            $invite = Invite::create($invitedUserId, $groupId);
            $this->inviteDao->insert($invite);

            if ($ownsTransaction) {
                $this->pdo->commit();
            }
    
            return $invite;
        } catch (Throwable $e) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function cancel(int $inviteId, int $userId) : void
    {
        $invite = $this->getInvite($inviteId);
        $group = $this->groupDao->findById($invite->getGroupId());

        if ($group === null || !$group->isAdmin($userId)) {
            throw new LogicException('Invite not found');
        }

        if (!$invite->isPending()) {
            throw new LogicException('Only pending invites can be canceled');
        }

        $this->inviteDao->delete($inviteId);
    }

    public function accept(int $inviteId, int $userId) : void
    {
        $invite = $this->getInviteForUser($inviteId, $userId);
        $group = $this->groupDao->findById($invite->getGroupId());

        if ($group === null) {
            throw new LogicException('Group not found');
        }

        $ownsTransaction = !$this->pdo->inTransaction();

        if ($ownsTransaction) {
            $this->pdo->beginTransaction();
        }

        try {
            $invite->accept();
            $group->addMember($userId);
    
            $this->groupDao->update($group);
            $this->inviteDao->update($invite);

            if ($ownsTransaction) {
                $this->pdo->commit();
            }
        } catch (Throwable $e) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function decline(int $inviteId, int $userId) : void
    {
        $invite = $this->getInviteForUser($inviteId, $userId);
        $invite->decline();
        $this->inviteDao->update($invite);
    }

    public function findForUser(int $inviteId, int $userId) : Invite
    {
        return $this->getInviteForUser($inviteId, $userId);
    }

    public function findForGroupAdmin(int $inviteId, int $userId) : Invite
    {
        $invite = $this->getInvite($inviteId);
        $group = $this->groupDao->findById($invite->getGroupId());

        if ($group === null || !$group->isAdmin($userId)) {
            throw new LogicException('Invite not found');
        }

        return $invite;
    }

    public function findAllForUser(int $invitedUserId) : array
    {
        return $this->inviteDao->findByInvitedUserId($invitedUserId);
    }

    public function findAllForUserByStatus(int $invitedUserId, InviteStatus $status) : array
    {
        return $this->inviteDao->findByInvitedUserIdAndInviteStatus($invitedUserId, $status);
    }

    public function findByGroupId(int $groupId, int $userId) : array
    {
        $group = $this->groupDao->findById($groupId);

        if ($group === null || !$group->isAdmin($userId)) {
            throw new LogicException('Group not found');
        }

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
            throw new LogicException('Invite not found');
        }

        return $invite;
    }
}