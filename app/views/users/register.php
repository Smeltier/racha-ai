<?php

$error = $error ?? null;
$name = $name ?? '';
$email = $email ?? '';
$csrfToken = $csrfToken ?? '';

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Racha Aí (Cadastro)</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="/style.css">
</head>
<body class="page page--center">
    <main>
        <form class="card card--auth" method="post" action="/register">
            <h1>Racha Aí</h1>
            <p class="subtitle">Crie sua conta para rachar suas despesas</p>

            <?php if ($error !== null): ?>
                <div class="alert" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

            <label class="sr-only" for="name">Nome</label>
            <input class="field" type="text" id="name" name="name" placeholder="Nome" value="<?= e($name) ?>" maxlength="150" autocomplete="name" required autofocus>

            <label class="sr-only" for="email">Email</label>
            <input class="field" type="email" id="email" name="email" placeholder="Email" value="<?= e($email) ?>" maxlength="320" autocomplete="email" required>

            <label class="sr-only" for="password">Senha</label>
            <input class="field" type="password" id="password" name="password" placeholder="Senha (mínimo 8 caracteres)" minlength="8" autocomplete="new-password" required>

            <button class="btn btn--block" type="submit">Criar conta</button>

            <p class="link muted">Já tem conta? <a href="/login">Entrar</a></p>
        </form>
    </main>
</body>
</html>
