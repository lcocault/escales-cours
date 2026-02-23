<?php
// public/admin/messages.php – list all general messages
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

$model    = new GeneralMessageModel();
$messages = $model->getAll();

$typeLabels = [
    'info'    => '💬 Information',
    'warning' => '⚠️ Avertissement',
    'danger'  => '🚨 Alerte',
    'success' => '✅ Bonne nouvelle',
];

$pageTitle = 'Messages généraux';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:.5rem">
        <h1 class="page-title" style="margin:0">📣 Messages généraux</h1>
        <a href="<?= APP_BASE_URL ?>/admin/message-edit.php" class="btn btn--primary">+ Nouveau message</a>
    </div>

    <?php if (empty($messages)): ?>
        <p>Aucun message. <a href="<?= APP_BASE_URL ?>/admin/message-edit.php">Créer le premier message</a>.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $m): ?>
                        <tr>
                            <td style="white-space:nowrap"><?= e(date('d/m/Y H:i', strtotime($m['created_at']))) ?></td>
                            <td><?= e($typeLabels[$m['type']] ?? $m['type']) ?></td>
                            <td><?= e($m['body']) ?></td>
                            <td>
                                <div class="actions">
                                    <a href="<?= APP_BASE_URL ?>/admin/message-edit.php?id=<?= (int) $m['id'] ?>" class="btn btn--warning btn--sm">Modifier</a>
                                    <form method="post" action="<?= APP_BASE_URL ?>/admin/message-delete.php" onsubmit="return confirm('Supprimer ce message ?')">
                                        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                        <button type="submit" class="btn btn--danger btn--sm">Supprimer</button>
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
