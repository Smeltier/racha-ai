<?php

namespace App\Services;

use DateTimeImmutable;
use App\Models\User\User;
use App\Models\User\Email;
use App\Models\User\UserDAO;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\ValidationException;

final class UserService
{
    private UserDAO $dao;

    public function __construct(UserDAO $dao)
    {
        $this->dao = $dao;
    }

    public function register(string $name, string $email, string $plainPassword) : User
    {
        $user = User::register($name, $email, $plainPassword);
        $this->dao->insert($user);
        return $user;
    }

    public function authenticate(string $email, string $plainPassword) : User
    {
        try {
            $user = $this->dao->findByEmail(Email::from($email));
        } catch (ValidationException) {
            throw new InvalidCredentialsException("Invalid email or password");
        }

        if ($user === null || !$user->verifyPassword($plainPassword)) {
            throw new InvalidCredentialsException("Invalid email or password");
        }

        return $user;
    }

    public function findById(int $id) : User
    {
        return $this->getActiveUser($id);
    }

    public function changeName(int $id, string $name) : User
    {
        $user = $this->getActiveUser($id);
        $user->changeName($name);
        $this->dao->update($user);
        return $user;
    }

    public function changeEmail(int $id, string $email) : User
    {
        $user = $this->getActiveUser($id);
        $user->changeEmail($email);
        $this->dao->update($user);
        return $user;
    }

    public function changePassword(int $id, string $currentPassword, string $newPassword) : User
    {
        $user = $this->getActiveUser($id);
        $user->changePassword($currentPassword, $newPassword);
        $this->dao->update($user);
        return $user;
    }

    public function remove(int $id) : void
    {
        $user = $this->getActiveUser($id);
        $user->delete(new DateTimeImmutable());
        $this->dao->update($user);
    }

    private function getActiveUser(int $id) : User
    {
        $user = $this->dao->findById($id);

        if ($user === null) {
            throw new UserNotFoundException("User not found");
        }

        return $user;
    }
}

