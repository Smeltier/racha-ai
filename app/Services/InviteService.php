<?php

namespace App\Services;

use App\Exceptions\StateException;
use Throwable;
use PDO;
use App\Exceptions\ValidationException;
use App\Models\User\Email;
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
            throw new StateException('Não é possível convidar a si mesmo para um grupo');
        }

        $group = $this->groupDao->findById($groupId);

        if ($group === null) {
            throw new StateException('Grupo não encontrado');
        }

        if (!$group->isAdmin($inviterUserId)) {
            throw new StateException('Somente administradores do grupo podem convidar usuários');
        }

        if ($this->userDao->findById($invitedUserId) === null) {
            throw new StateException('Usuário não encontrado');
        }

        if ($group->hasMember($invitedUserId)) {
            throw new StateException('O usuário já é membro do grupo');
        }

        $this->inviteDao->expirePending();

        $ownsTransaction = !$this->pdo->inTransaction();

        if ($ownsTransaction) {
            $this->pdo->beginTransaction();
        }

        try {
            $existingInvite = $this->inviteDao->findByInvitedUserIdAndGroupId($invitedUserId, $groupId);
    
            if ($existingInvite !== null) {
                if ($existingInvite->isPending()) {
                    throw new StateException('Já existe um convite pendente para este usuário e grupo');
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

    public function createForEmail(int $inviterUserId, string $email, int $groupId) : Invite
    {
        try {
            $user = $this->userDao->findByEmail(Email::from($email));
        } catch (ValidationException $e) {
            throw new StateException('Endereço de e-mail inválido', 0, $e);
        }

        if ($user === null || $user->getId() === null) {
            throw new StateException('Usuário não encontrado');
        }

        return $this->create($inviterUserId, $user->getId(), $groupId);
    }

    public function cancel(int $inviteId, int $userId) : void
    {
        $invite = $this->getInvite($inviteId);
        $group = $this->groupDao->findById($invite->getGroupId());

        if ($group === null || !$group->isAdmin($userId)) {
            throw new StateException('Convite não encontrado');
        }

        if (!$invite->isPending()) {
            throw new StateException('Somente convites pendentes podem ser cancelados');
        }

        $this->inviteDao->delete($inviteId);
    }

    public function accept(int $inviteId, int $userId) : void
    {
        $invite = $this->getInviteForUser($inviteId, $userId);
        $group = $this->groupDao->findById($invite->getGroupId());

        if ($group === null) {
            throw new StateException('Grupo não encontrado');
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
            throw new StateException('Convite não encontrado');
        }

        return $invite;
    }

    public function findAllForUser(int $userId) : array
    {
        $this->inviteDao->expirePending();
        return $this->inviteDao->findByInvitedUserId($userId);
    }

    public function findAllForUserView(int $userId) : array
    {
        $this->inviteDao->expirePending();
        return $this->inviteDao->findViewByInvitedUserId($userId);
    }

    public function findAllForUserByStatus(int $userId, InviteStatus $status) : array
    {
        $this->inviteDao->expirePending();
        return $this->inviteDao->findByInvitedUserIdAndInviteStatus($userId, $status);
    }

    public function findByGroupId(int $groupId, int $userId) : array
    {
        $this->inviteDao->expirePending();

        $group = $this->groupDao->findById($groupId);

        if ($group === null || !$group->isAdmin($userId)) {
            throw new StateException('Grupo não encontrado');
        }

        return $this->inviteDao->findByGroupId($groupId);
    }

    public function findByGroupIdView(int $groupId, int $userId) : array
    {
        $this->inviteDao->expirePending();

        $group = $this->groupDao->findById($groupId);

        if ($group === null || !$group->isAdmin($userId)) {
            throw new StateException('Grupo não encontrado');
        }

        return $this->inviteDao->findViewByGroupId($groupId);
    }

    private function getInvite(int $inviteId) : Invite
    {
        $this->inviteDao->expirePending();

        $invite = $this->inviteDao->findById($inviteId);

        if ($invite === null) {
            throw new StateException('Convite não encontrado');
        }

        return $invite;
    }

    private function getInviteForUser(int $inviteId, int $userId) : Invite
    {
        $invite = $this->getInvite($inviteId);

        if ($invite->getInvitedUserId() !== $userId) {
            throw new StateException('Convite não encontrado');
        }

        return $invite;
    }
}