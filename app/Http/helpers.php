<?php

use App\Http\Csrf;

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function app_base_path(): string
{
    $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    $directory = str_replace('\\', '/', dirname($scriptName));

    if ($directory === '.' || $directory === '/') {
        return '';
    }

    return '/' . trim($directory, '/');
}

function app_url(string $path): string
{
    return app_base_path() . '/' . ltrim($path, '/');
}

function csrf_field() : string
{
    return '<input type="hidden" name="csrf_token" value="' . e((new Csrf())->token()) . '">';
}
