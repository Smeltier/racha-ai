<?php

$title = $title ?? 'Racha Aí';
$bodyClass = $bodyClass ?? 'page';
$showNavbar = $showNavbar ?? false;
$csrfToken = $csrfToken ?? '';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="/style.css">
</head>
<body class="<?= e($bodyClass) ?>">
<?php if ($showNavbar): ?>
    <header class="navbar">
        <span class="navbar__brand">Racha Aí</span>
        <nav class="navbar__links">
            <a href="/profile" aria-current="page">Perfil</a>
        </nav>
        <form method="post" action="/logout">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <button class="btn btn--sm btn--ghost" type="submit">Sair</button>
        </form>
    </header>
<?php endif; ?>
