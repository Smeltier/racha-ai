<?php

$error = $error ?? null;
$email = $email ?? '';
$csrfToken = $csrfToken ?? '';

$title = 'Racha Aí (Login)';
$bodyClass = 'page page--center';

require __DIR__ . '/../partials/header.php';

?>

<main>
    <form class="card card--auth" method="post" action="/login">
        <h1>Racha Aí</h1>
        <p class="subtitle">Entre para rachar suas despesas</p>

        <?php if ($error !== null): ?>
            <div class="alert" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

        <label class="sr-only" for="email">Email</label>
        <input class="field" type="email" id="email" name="email" placeholder="Email" value="<?= e($email) ?>" autocomplete="username" required autofocus>

        <label class="sr-only" for="password">Senha</label>
        <input class="field" type="password" id="password" name="password" placeholder="Senha" autocomplete="current-password" required>

        <button class="btn btn--block" type="submit">Entrar</button>

        <p class="link muted">Novo membro? <a href="/register">Crie uma conta</a></p>
    </form>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
