<?php
// public/admin/index.php – admin dashboard
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

$db = Database::getInstance();
$sessionCount  = (int) $db->query("SELECT COUNT(*) FROM sessions WHERE deleted_at IS NULL")->fetchColumn();
$userCount     = (int) $db->query("SELECT COUNT(*) FROM users WHERE deleted_at IS NULL")->fetchColumn();
$bookingCount  = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE status IN ('confirmed','attended')")->fetchColumn();

$pageTitle = 'Administration';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <h1 class="page-title">⚙️ Administration</h1>

    <div class="admin-grid">
        <a href="<?= APP_BASE_URL ?>/admin/sessions.php" class="admin-card" style="text-decoration:none;color:inherit">
            <div class="admin-card__icon">📅</div>
            <div class="admin-card__label">Séances</div>
            <div style="color:var(--color-muted);font-size:.9rem;margin-top:.25rem"><?= $sessionCount ?> séance(s)</div>
        </a>
        <a href="<?= APP_BASE_URL ?>/admin/sessions.php" class="admin-card" style="text-decoration:none;color:inherit">
            <div class="admin-card__icon">👥</div>
            <div class="admin-card__label">Participants</div>
            <div style="color:var(--color-muted);font-size:.9rem;margin-top:.25rem"><?= $userCount ?> inscrit(s)</div>
        </a>
        <a href="<?= APP_BASE_URL ?>/admin/sessions.php" class="admin-card" style="text-decoration:none;color:inherit">
            <div class="admin-card__icon">✅</div>
            <div class="admin-card__label">Réservations</div>
            <div style="color:var(--color-muted);font-size:.9rem;margin-top:.25rem"><?= $bookingCount ?> confirmée(s)</div>
        </a>
    </div>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
