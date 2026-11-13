<?php

/** @var int $groupId */
/** @var \App\Models\Category\Category[] $categories */

$categories = $categories ?? [];
$errorMessage = $errorMessage ?? null;

$title = 'Racha Aí (Categorias)';
$showNavbar = true;

require __DIR__ . '/../partials/header.php';

?>

<main class="container">
    <section class="card form">
        <h1>Categorias</h1>
        <p class="muted">Crie e organize as categorias do grupo.</p>

        <?php if ($errorMessage !== null): ?>
            <div class="alert" role="alert">
                <?= e($errorMessage) ?>
            </div>
        <?php endif; ?>

        <form class="form"
              method="post"
              action="<?= e(app_url('/groups/' . $groupId . '/categories')) ?>">
            <?= csrf_field() ?>

            <div class="form__group">
                <label class="label" for="name">Nome</label>
                <input class="field"
                       type="text"
                       id="name"
                       name="name"
                       maxlength="50"
                       required>
            </div>

            <div class="form__group">
                <label class="label" for="color">Cor hexadecimal</label>
                <input class="field"
                       type="text"
                       id="color"
                       name="color"
                       placeholder="FF5733"
                       pattern="[A-Fa-f0-9]{6}"
                       maxlength="6"
                       required>
            </div>

            <button class="btn btn--sm" type="submit">
                Criar categoria
            </button>
        </form>
    </section>

    <section class="card">
        <h2>Categorias cadastradas</h2>

        <?php if ($categories === []): ?>
            <p class="muted">Nenhuma categoria cadastrada.</p>
        <?php else: ?>
            <?php foreach ($categories as $category): ?>
                <div class="list-item">
                    <div>
                        <div class="list-item__title">
                            <span
                                style="display:inline-block;width:14px;height:14px;border-radius:50%;background:#<?= e($category->getColor()) ?>;">
                            </span>
                            <?= e($category->getName()) ?>
                        </div>

                        <div class="muted">
                            #<?= e($category->getColor()) ?>
                        </div>
                    </div>

                    <div class="member-actions">
                        <form method="post"
                              action="<?= e(app_url('/groups/' . $groupId . '/categories/' . $category->getId() . '/rename')) ?>">
                            <?= csrf_field() ?>
                            <input class="field"
                                   type="text"
                                   name="name"
                                   value="<?= e($category->getName()) ?>"
                                   maxlength="50"
                                   required>
                            <button class="btn btn--sm" type="submit">
                                Renomear
                            </button>
                        </form>

                        <form method="post"
                              action="<?= e(app_url('/groups/' . $groupId . '/categories/' . $category->getId() . '/recolor')) ?>">
                            <?= csrf_field() ?>
                            <input class="field"
                                   type="text"
                                   name="color"
                                   value="<?= e($category->getColor()) ?>"
                                   pattern="[A-Fa-f0-9]{6}"
                                   maxlength="6"
                                   required>
                            <button class="btn btn--sm btn--ghost" type="submit">
                                Alterar cor
                            </button>
                        </form>

                        <form method="post"
                              action="<?= e(app_url('/groups/' . $groupId . '/categories/' . $category->getId() . '/delete')) ?>"
                              onsubmit="return confirm('Excluir esta categoria?')">
                            <?= csrf_field() ?>
                            <button class="btn btn--sm btn--danger" type="submit">
                                Excluir
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>