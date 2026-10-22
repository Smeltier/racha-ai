<?php

use App\Http\Csrf;

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_field() : string
{
    return '<input type="hidden" name="csrf_token" value="' . e((new Csrf())->token()) . '">';
}
