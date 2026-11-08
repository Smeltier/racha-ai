<?php

/** @var \App\Models\Group\Group $group */

$error = $error ?? null;
$name = $name ?? $group->getName();

$title = 'Racha Aí (Editar grupo)';
$showNavbar = true;

require __DIR__ . '/../partials/header.php';

?>

<main class="container">
    <section class="card form">
        <div>
            <h1>Editar grupo</h1>
            <p class="muted">Atualize as informações do grupo.</p>
        </div>

        <?php if ($error !== null): ?>
            <div class="alert" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form class="form" method="post" action="<?= e(app_url('/groups/' . $group->getId())) ?>">
            <?= csrf_field() ?>

            <div class="form__group">
                <label class="label" for="name">Nome do grupo</label>
                <input class="field" type="text" id="name" name="name" value="<?= e($name) ?>" maxlength="200" required autofocus>
            </div>

            <div class="row">
                <button class="btn btn--sm" type="submit">Salvar alterações</button>
                <a class="btn btn--sm btn--ghost" href="<?= e(app_url('/groups/' . $group->getId())) ?>">Cancelar</a>
            </div>
        </form>

        <form method="post"
              action="<?= e(app_url('/groups/' . $group->getId() . '/delete')) ?>"
              onsubmit="return confirm('Excluir este grupo?')">
            <?= csrf_field() ?>
            <button class="btn btn--sm btn--danger" type="submit">Excluir grupo</button>
        </form>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
