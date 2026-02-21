<?php
// public/admin/sessions.php – list all sessions
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

$sessionModel = new SessionModel();
$sessions = $sessionModel->getAll();

$pageTitle = 'Gérer les séances';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:.5rem">
        <h1 class="page-title" style="margin:0">📅 Séances</h1>
        <a href="<?= APP_BASE_URL ?>/admin/session-edit.php" class="btn btn--primary">+ Nouvelle séance</a>
    </div>

    <?php if (empty($sessions)): ?>
        <p>Aucune séance. <a href="<?= APP_BASE_URL ?>/admin/session-edit.php">Créer la première séance</a>.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Titre</th>
                        <th>Thème</th>
                        <th>Places</th>
                        <th>Prix</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $s): ?>
                        <tr>
                            <td><?= e($s['session_date']) ?></td>
                            <td><?= e($s['title']) ?></td>
                            <td><?= e($s['theme']) ?></td>
                            <td><?= (int) $s['remaining_seats'] ?> / <?= (int) $s['max_attendees'] ?></td>
                            <td><?= e(formatPrice((int) $s['price_cents'])) ?></td>
                            <td>
                                <div class="actions">
                                    <a href="<?= APP_BASE_URL ?>/admin/session-edit.php?id=<?= (int) $s['id'] ?>" class="btn btn--warning btn--sm">Modifier</a>
                                    <a href="<?= APP_BASE_URL ?>/admin/attendees.php?session_id=<?= (int) $s['id'] ?>" class="btn btn--secondary btn--sm">Participants</a>
                                    <form method="post" action="<?= APP_BASE_URL ?>/admin/session-delete.php" onsubmit="return confirm('Supprimer cette séance ?')">
                                        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
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
