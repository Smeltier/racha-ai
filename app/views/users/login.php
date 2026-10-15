<?php

$error = $error ?? null;
$email = $email ?? '';
$csrfToken = $csrfToken ?? '';

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Racha Aí (Login)</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="/style.css">
</head>
<body class="page page--center">
    <main>
        <form class="card card--auth" method="post" action="/login">
            <h1>Racha Aí</h1>
            <p class="subtitle">Entre para rachar suas despesas</p>

            <?php if ($error !== null): ?>
                <div class="alert" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

            <label class="sr-only" for="email">Email</label>
            <input class="field" type="email" id="email" name="email" placeholder="Email" value="<?= e($email) ?>" autocomplete="email" required autofocus>

            <label class="sr-only" for="password">Senha</label>
            <input class="field" type="password" id="password" name="password" placeholder="Senha" autocomplete="current-password" required>

            <button class="btn btn--block" type="submit">Entrar</button>

            <p class="link muted" style="margin-bottom:0">Novo membro? <a href="/register">Crie uma conta</a></p>
        </form>
    </main>
</body>
</html>
