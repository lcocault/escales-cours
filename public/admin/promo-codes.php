<?php
// public/admin/promo-codes.php – list all promotional codes
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

$promoModel = new PromoCodeModel();
$codes = $promoModel->getAll();

$pageTitle = 'Codes promotionnels';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:.5rem">
        <h1 class="page-title" style="margin:0">🏷️ Codes promotionnels</h1>
        <a href="<?= APP_BASE_URL ?>/admin/promo-code-edit.php" class="btn btn--primary">+ Nouveau code</a>
    </div>

    <?php if (empty($codes)): ?>
        <p>Aucun code promotionnel. <a href="<?= APP_BASE_URL ?>/admin/promo-code-edit.php">Créer le premier code</a>.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Remise</th>
                        <th>Séance concernée</th>
                        <th>Utilisations</th>
                        <th>Expiration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($codes as $c): ?>
                        <?php
                            $maxUses    = $c['max_uses'] !== null ? (int) $c['max_uses'] : null;
                            $usageLabel = $maxUses !== null
                                ? (int) $c['used_count'] . ' / ' . $maxUses
                                : (int) $c['used_count'] . ' (illimité)';
                            $expired    = $c['expires_at'] !== null && strtotime($c['expires_at']) < time();
                        ?>
                        <tr>
                            <td><code><?= e($c['code']) ?></code></td>
                            <td><?= e(formatPrice((int) $c['discount_cents'])) ?></td>
                            <td><?= $c['session_title'] ? e($c['session_title']) : '<span style="color:var(--color-muted)">Toutes les séances</span>' ?></td>
                            <td><?= e($usageLabel) ?></td>
                            <td>
                                <?php if ($c['expires_at']): ?>
                                    <span <?= $expired ? 'style="color:var(--color-danger,#dc2626)"' : '' ?>>
                                        <?= e(date('d/m/Y', strtotime($c['expires_at']))) ?>
                                        <?= $expired ? '⚠️ expiré' : '' ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color:var(--color-muted)">Aucune</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="<?= APP_BASE_URL ?>/admin/promo-code-edit.php?id=<?= (int) $c['id'] ?>" class="btn btn--warning btn--icon" title="Modifier" aria-label="Modifier">✏️</a>
                                    <form method="post" action="<?= APP_BASE_URL ?>/admin/promo-code-delete.php" onsubmit="return confirm('Supprimer ce code promotionnel ?')">
                                        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                        <button type="submit" class="btn btn--danger btn--icon" title="Supprimer" aria-label="Supprimer">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
