<?php

$error = $error ?? null;
$name = $name ?? '';

$title = 'Racha Aí (Criar grupo)';
$showNavbar = true;

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

require __DIR__ . '/../partials/header.php';

?>

<main class="container">
    <section class="card form">
        <div>
            <h1>Criar grupo</h1>
            <p class="muted">Crie um grupo para começar a organizar as despesas compartilhadas.</p>
        </div>

        <?php if ($error !== null): ?>
            <div class="alert" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form class="form" method="post" action="<?= e($base . '/groups') ?>">
            <?= csrf_field() ?>

            <div class="form__group">
                <label class="label" for="name">Nome do grupo</label>
                <input class="field" type="text" id="name" name="name" value="<?= e($name) ?>" maxlength="200" required autofocus>
                <div class="muted">O nome deve ter entre 1 e 200 caracteres.</div>
            </div>

            <div class="row">
                <button class="btn btn--sm" type="submit">Criar grupo</button>
                <button class="btn btn--sm btn--ghost" type="button" onclick="window.location.href='<?= e($base . '/groups/') ?>'">Cancelar</button>
            </div>
        </form>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
