<?php

/** @var \App\Services\InviteView[] $invites */

$invites = $invites ?? [];
$error = $error ?? null;

$title = 'Racha Aí (Convites)';
$showNavbar = true;

require __DIR__ . '/../partials/header.php';

?>

<main class="container">
    <section class="card">
        <h1>Meus convites</h1>
        <p class="muted">Convites para participar de grupos.</p>

        <?php if ($error !== null): ?>
            <div class="alert" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
    </section>

    <section class="card">
        <?php if ($invites === []): ?>
            <p class="muted">Você não possui convites.</p>
        <?php else: ?>
            <?php foreach ($invites as $inviteView): ?>
                <?php $invite = $inviteView->invite; ?>
                <div class="list-item invite-list-item">
                    <div>
                        <div class="list-item__title">
                            <?= e($inviteView->groupName) ?>
                        </div>

                        <div class="muted">
                            Status: <?= e($invite->getStatus()->value) ?>
                        </div>

                        <div class="muted">
                            Expira em
                            <?= e($invite->getExpiresAt()->format('d/m/Y H:i')) ?>
                        </div>
                    </div>

                    <?php if ($invite->isPending()): ?>
                        <div class="row">
                            <form method="post"
                                  action="<?= e(app_url('/invites/' . $invite->getId() . '/accept')) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn--sm" type="submit">
                                    Aceitar
                                </button>
                            </form>

                            <form method="post"
                                  action="<?= e(app_url('/invites/' . $invite->getId() . '/decline')) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn--sm btn--ghost" type="submit">
                                    Recusar
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>