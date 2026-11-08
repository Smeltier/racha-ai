<?php

/** @var int $userId */
/** @var \App\Models\Group\Group $group */

$title = 'Racha Aí (' . $group->getName() . ')';
$showNavbar = true;

require __DIR__ . '/../partials/header.php';

?>

<main class="container">
    <section class="card">
        <a href="<?= e(app_url('/groups')) ?>">← Voltar para grupos</a>
        <h1><?= e($group->getName()) ?></h1>
        <p class="muted">
            Criado em <?= e($group->getCreatedAt()->format('d/m/Y H:i')) ?>
        </p>
        <?php if ($group->isAdmin($userId)): ?>
            <a class="btn btn--sm group-actions__invite" href="<?= e(app_url('/groups/' . $group->getId() . '/invites')) ?>">
                Gerenciar convites
            </a>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Membros (<?= $group->memberCount() ?>)</h2>

        <?php foreach ($group->getMembers() as $member): ?>
            <div class="list-item">
                <div>
                    <div class="list-item__title">Usuário: <?= e((string) $member->userId) ?></div>
                    <div class="muted">
                        Desde <?= e($member->joinedAt->format('d/m/Y')) ?>
                    </div>
                </div>
                <span class="badge">
                    <?= $member->isAdmin() ? 'Administrador' : 'Membro' ?>
                </span>
            </div>
        <?php endforeach; ?>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
