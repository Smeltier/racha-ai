<?php

/** @var \App\Models\Group\Group[] $groups */

$groups = $groups ?? [];
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

$title = 'Racha Aí (Grupos)';
$showNavbar = true;

require __DIR__ . '/../partials/header.php';

?>

<main class="container">
    <section class="row">
        <div>
            <h1>Meus grupos</h1>
            <p class="muted">Organize as despesas compartilhadas em um só lugar.</p>
        </div>
        <a class="btn btn--sm" href="/groups/create">Criar grupo</a>
    </section>

    <section class="card">
        <h2>Grupos dos quais participo</h2>

        <?php if ($groups === []): ?>
            <p class="muted">Você ainda não participa de nenhum grupo.</p>
        <?php else: ?>
            <?php foreach ($groups as $group): ?>
                <div class="list-item">
                    <div>
                        <div class="list-item__title">
                            <a href="<?= e($base . '/groups/' . $group->getId()) ?>">
                                <?= e($group->getName()) ?>
                            </a>
                        </div>
                        <div class="muted"><?= $group->memberCount() ?> participante(s)</div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
