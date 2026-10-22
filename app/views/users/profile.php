<?php

/** @var \App\Models\User\User $user */

$profileError = $profileError ?? null;
$passwordError = $passwordError ?? null;
$initial = mb_strtoupper(mb_substr($user->getName(), 0, 1));

$title = 'Racha Aí (Perfil)';
$showNavbar = true;

require __DIR__ . '/../partials/header.php';

?>

<main class="container">
    <section class="card row">
        <div class="list-item__main">
            <div class="avatar"><?= e($initial) ?></div>
            <div>
                <h1><?= e($user->getName()) ?></h1>
                <div class="muted"><?= e($user->getEmail()->getValue()) ?></div>
            </div>
        </div>
    </section>

    <section class="card form">
        <h2>Dados pessoais</h2>

        <?php if ($profileError !== null): ?>
            <div class="alert" role="alert"><?= e($profileError) ?></div>
        <?php endif; ?>

        <form class="form" method="post" action="profile">
            <?= csrf_field() ?>

            <div class="form__group">
                <label class="label" for="name">Nome</label>
                <input class="field" type="text" id="name" name="name" value="<?= e($user->getName()) ?>" maxlength="150" autocomplete="name" required>
            </div>

            <div class="form__group">
                <label class="label" for="email">Email</label>
                <input class="field" type="email" id="email" name="email" value="<?= e($user->getEmail()->getValue()) ?>" maxlength="320" autocomplete="email" required>
            </div>

            <button class="btn btn--sm form__submit" type="submit">Salvar alterações</button>
        </form>
    </section>

    <section class="card form">
        <h2>Alterar senha</h2>

        <?php if ($passwordError !== null): ?>
            <div class="alert" role="alert"><?= e($passwordError) ?></div>
        <?php endif; ?>

        <form class="form" method="post" action="profile/password">
            <?= csrf_field() ?>

            <div class="form__group">
                <label class="label" for="current_password">Senha atual</label>
                <input class="field" type="password" id="current_password" name="current_password" autocomplete="current-password" required>
            </div>

            <div class="form__group">
                <label class="label" for="new_password">Nova senha</label>
                <input class="field" type="password" id="new_password" name="new_password" minlength="8" autocomplete="new-password" required>
            </div>

            <button class="btn btn--sm form__submit" type="submit">Alterar senha</button>
        </form>
    </section>

    <section class="card form">
        <h2>Excluir conta</h2>
        <p class="muted">Essa ação é permanente e não pode ser desfeita.</p>

        <form method="post" action="profile/delete" onsubmit="return confirm('Excluir sua conta permanentemente?')">
            <?= csrf_field() ?>
            <button class="btn btn--sm btn--danger" type="submit">Excluir minha conta</button>
        </form>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
