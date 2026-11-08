<?php

/** @var \App\Models\Invite\Invite[] $invites */
/** @var int $groupId */

$invites = $invites ?? [];
$error = $error ?? null;
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

$title = 'Racha Aí (Convites do grupo)';
$showNavbar = true;

require __DIR__ . '/../partials/header.php';

?>

<main class="container">
    <section class="card form">
        <h1>Convidar para o grupo</h1>
        <p class="muted">Informe o email de uma pessoa cadastrada.</p>

        <?php if ($error !== null): ?>
            <div class="alert" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form class="form"
              method="post"
              action="<?= e($base . '/groups/' . $groupId . '/invites') ?>">
            <?= csrf_field() ?>

            <div class="form__group">
                <label class="label" for="email">Email</label>
                <input
                    class="field"
                    type="email"
                    id="email"
                    name="email"
                    maxlength="320"
                    autocomplete="email"
                    required
                >
            </div>

            <button class="btn btn--sm" type="submit">
                Enviar convite
            </button>
        </form>
    </section>

    <section class="card">
        <h2>Convites enviados</h2>

        <?php if ($invites === []): ?>
            <p class="muted">Nenhum convite foi enviado para este grupo.</p>
        <?php else: ?>
            <?php foreach ($invites as $invite): ?>
                <div class="list-item">
                    <div>
                        <div class="list-item__title">
                            Usuário #<?= e((string) $invite->getInvitedUserId()) ?>
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
                        <form method="post"
                              action="<?= e($base . '/invites/' . $invite->getId() . '/cancel') ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn--sm btn--danger" type="submit">
                                Cancelar
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>