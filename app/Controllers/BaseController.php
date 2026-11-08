<?php

namespace App\Controllers;

use App\Http\Session;

abstract class BaseController
{
    public function __construct(
        protected Session $session
    ) {}

    protected function requireLogin() : int
    {
        $userId = $this->session->userId();

        if ($userId === null) {
            $this->redirect('/login');
        }

        return $userId;
    }

    protected function input(string $key) : string
    {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? $value : '';
    }

    protected function redirect(string $path): never
    {
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        header('Location: ' . $base . $path);
        exit;
    }

    protected function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.php';
    }
}