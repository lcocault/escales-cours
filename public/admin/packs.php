<?php
// public/admin/packs.php – list all packs
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

$packModel = new PackModel();
$packs = $packModel->getAll();

$pageTitle = 'Gérer les packs';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:.5rem">
        <h1 class="page-title" style="margin:0">📦 Packs</h1>
        <a href="<?= APP_BASE_URL ?>/admin/pack-edit.php" class="btn btn--primary">+ Nouveau pack</a>
    </div>

    <?php if (empty($packs)): ?>
        <p>Aucun pack. <a href="<?= APP_BASE_URL ?>/admin/pack-edit.php">Créer le premier pack</a>.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Prix</th>
                        <th>Séances</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packs as $p): ?>
                        <tr>
                            <td><?= e($p['title']) ?></td>
                            <td><?= e(formatPrice((int) $p['price_cents'])) ?></td>
                            <td><?= (int) $p['session_count'] ?></td>
                            <td>
                                <div class="actions">
                                    <a href="<?= APP_BASE_URL ?>/admin/pack-edit.php?id=<?= (int) $p['id'] ?>" class="btn btn--warning btn--icon" title="Modifier" aria-label="Modifier">✏️</a>
                                    <a href="<?= APP_BASE_URL ?>/pack.php?id=<?= (int) $p['id'] ?>" class="btn btn--secondary btn--icon" title="Voir" aria-label="Voir" target="_blank">👁️</a>
                                    <form method="post" action="<?= APP_BASE_URL ?>/admin/pack-delete.php" onsubmit="return confirm('Supprimer ce pack ?')">
                                        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
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
