<?php

namespace App\Controllers;

use App\Http\Session;
use App\Services\UserService;
use App\Exceptions\EmailAlreadyInUseException;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\ValidationException;

final class UserController
{
    private UserService $userService;
    private Session $session;

    public function __construct(UserService $userService, Session $session)
    {
        $this->userService = $userService;
        $this->session = $session;
    }

    public function showRegister(): void
    {
        $this->render('users/register');
    }

    public function register(): void
    {
        $name = $this->input('name');
        $email = $this->input('email');

        try {
            $user = $this->userService->register($name, $email, $this->input('password'));
        } catch (ValidationException | EmailAlreadyInUseException $e) {
            $this->render('users/register', [
                'error' => $e->getMessage(),
                'name' => $name,
                'email' => $email,
            ]);
            return;
        }

        $this->session->login($user->getId());
        $this->redirect('/profile');
    }

    public function showLogin(): void
    {
        $this->render('users/login');
    }

    public function login(): void
    {
        $email = $this->input('email');

        try {
            $user = $this->userService->authenticate($email, $this->input('password'));
        } catch (InvalidCredentialsException $e) {
            $this->render('users/login', [
                'error' => $e->getMessage(),
                'email' => $email,
            ]);
            return;
        }

        $this->session->login($user->getId());
        $this->redirect('/profile');
    }

    public function logout(): void
    {
        $this->requireLogin();

        $this->session->logout();
        $this->redirect('/login');
    }

    public function showProfile(): void
    {
        $userId = $this->requireLogin();

        $this->renderProfile($userId);
    }

    public function updateProfile(): void
    {
        $userId = $this->requireLogin();

        try {
            $this->userService->changeName($userId, $this->input('name'));
            $this->userService->changeEmail($userId, $this->input('email'));
        } catch (UserNotFoundException) {
            $this->forceLogout();
        } catch (ValidationException | EmailAlreadyInUseException $e) {
            $this->renderProfile($userId, ['profileError' => $e->getMessage()]);
            return;
        }

        $this->redirect('/profile');
    }

    public function updatePassword(): void
    {
        $userId = $this->requireLogin();

        try {
            $this->userService->changePassword(
                $userId,
                $this->input('current_password'),
                $this->input('new_password')
            );
        } catch (UserNotFoundException) {
            $this->forceLogout();
        } catch (ValidationException $e) {
            $this->renderProfile($userId, ['passwordError' => $e->getMessage()]);
            return;
        }

        $this->redirect('/profile');
    }

    public function delete(): void
    {
        $userId = $this->requireLogin();

        try {
            $this->userService->remove($userId);
        } catch (UserNotFoundException) {
        }

        $this->forceLogout();
    }

    private function requireLogin(): int
    {
        $userId = $this->session->userId();

        if ($userId === null) {
            $this->redirect('/login');
        }

        return $userId;
    }

    private function forceLogout(): never
    {
        $this->session->logout();
        $this->redirect('/login');
    }

    private function renderProfile(int $userId, array $data = []): void
    {
        try {
            $user = $this->userService->findById($userId);
        } catch (UserNotFoundException) {
            $this->forceLogout();
        }

        $this->render('users/profile', ['user' => $user] + $data);
    }

    private function input(string $key): string
    {
        $value = $_POST[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    private function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.php';
    }
}
