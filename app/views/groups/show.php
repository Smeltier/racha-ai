<?php

/** @var int $userId */
/** @var \App\Services\GroupView $groupView */

$group = $groupView->group;
$title = 'Racha Aí (' . $group->getName() . ')';
$showNavbar = true;
$error = $error ?? null;

require __DIR__ . '/../partials/header.php';

?>

<main class="container">
    <section class="card">
        <a href="<?= e(app_url('/groups')) ?>">← Voltar para grupos</a>
        <h1><?= e($group->getName()) ?></h1>
        <p class="muted">
            Criado em <?= e($group->getCreatedAt()->format('d/m/Y H:i')) ?>
        </p>
        <?php if ($error !== null): ?>
            <div class="alert" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($group->isAdmin($userId)): ?>
            <div class="group-actions">
                <a class="btn btn--sm" href="<?= e(app_url('/groups/' . $group->getId() . '/invites')) ?>">
                    Gerenciar convites
                </a>
                <a class="btn btn--sm btn--ghost" href="<?= e(app_url('/groups/' . $group->getId() . '/edit')) ?>">
                    Editar grupo
                </a>
                <a class="btn btn--sm" href="<?= e(app_url('/groups/' . $group->getId() . '/categories')) ?>">
                    Gerenciar categorias
                </a>
                <a class="btn btn--sm btn--danger" href="<?= e(app_url('/groups/' . $group->getId() . '/leave')) ?>">
                    Sair do grupo
                </a>
            </div>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Membros (<?= $group->memberCount() ?>)</h2>

        <?php foreach ($groupView->members as $memberView): ?>
            <?php $member = $memberView->member; ?>
            <div class="list-item">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div>
                        <div class="list-item__title"><?= e($memberView->userName) ?></div>
                        <div class="muted">
                            Desde <?= e($member->joinedAt->format('d/m/Y')) ?>
                        </div>
                    </div>
                    <span class="badge">
                        <?= $member->isAdmin() ? 'Administrador' : 'Membro' ?>
                    </span>
                </div>
                <?php if ($group->isAdmin($userId) && $member->userId !== $userId): ?>
                    <div class="member-actions">
                        <?php if ($member->isAdmin()): ?>
                            <form method="post" action="<?= e(app_url('/groups/' . $group->getId() . '/members/' . $member->userId . '/demote')) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn--sm btn--ghost" type="submit">Rebaixar</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="<?= e(app_url('/groups/' . $group->getId() . '/members/' . $member->userId . '/promote')) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn--sm" type="submit">Promover</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="<?= e(app_url('/groups/' . $group->getId() . '/members/' . $member->userId . '/remove')) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn--sm btn--danger" type="submit">Remover</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
