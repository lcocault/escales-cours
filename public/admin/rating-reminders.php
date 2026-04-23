<?php
// public/admin/rating-reminders.php – list attended bookings with no rating
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

$bookingModel = new BookingModel();
$rows = $bookingModel->getAttendancesWithoutRating();

$pageTitle = 'Rappels d\'avis';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <h1 class="page-title">⭐ Rappels d'avis</h1>
    <p style="color:var(--color-muted);margin-bottom:1.5rem">
        Participations sans évaluation. Vous pouvez envoyer un rappel par e-mail ou ignorer définitivement l'entrée.
    </p>

    <?php if (empty($rows)): ?>
        <p>🎉 Aucune participation en attente d'évaluation.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Participant</th>
                        <th>E-mail</th>
                        <th>Séance</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= e($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td><?= e($row['email']) ?></td>
                            <td><?= e($row['session_title']) ?></td>
                            <td><?= e(formatDate($row['session_date'])) ?></td>
                            <td>
                                <div class="actions">
                                    <form method="post" action="<?= APP_BASE_URL ?>/admin/rating-reminder-action.php">
                                        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                                        <input type="hidden" name="booking_id" value="<?= (int) $row['id'] ?>">
                                        <input type="hidden" name="action" value="remind">
                                        <button type="submit" class="btn btn--primary btn--sm">📧 Rappel</button>
                                    </form>
                                    <form method="post" action="<?= APP_BASE_URL ?>/admin/rating-reminder-action.php"
                                          onsubmit="return confirm('Ignorer cette participation ? Elle n\'apparaîtra plus dans la liste.')">
                                        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                                        <input type="hidden" name="booking_id" value="<?= (int) $row['id'] ?>">
                                        <input type="hidden" name="action" value="dismiss">
                                        <button type="submit" class="btn btn--secondary btn--sm">✖ Ignorer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <p class="mt-2"><a href="<?= APP_BASE_URL ?>/admin/index.php">← Retour au tableau de bord</a></p>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
