<?php

$error = $error ?? null;
$name = $name ?? '';

$title = 'Racha Aí (Criar grupo)';
$showNavbar = true;

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

        <form class="form" method="post" action="/groups">
            <?= csrf_field() ?>

            <div class="form__group">
                <label class="label" for="name">Nome do grupo</label>
                <input class="field" type="text" id="name" name="name" value="<?= e($name) ?>" maxlength="200" required autofocus>
                <div class="muted">O nome deve ter entre 1 e 200 caracteres.</div>
            </div>

            <div class="row">
                <button class="btn btn--sm" type="submit">Criar grupo</button>
                <a class="btn btn--sm btn--ghost" href="/groups">Cancelar</a>
            </div>
        </form>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
