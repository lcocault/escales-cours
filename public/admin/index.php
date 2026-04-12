<?php
// public/admin/index.php – admin dashboard
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

$db = Database::getInstance();
$sessionCount  = (int) $db->query("SELECT COUNT(*) FROM sessions WHERE deleted_at IS NULL")->fetchColumn();
$userCount     = (int) $db->query("SELECT COUNT(*) FROM users WHERE deleted_at IS NULL")->fetchColumn();
$bookingCount  = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE status IN ('confirmed','attended')")->fetchColumn();
$messageCount  = (int) $db->query("SELECT COUNT(*) FROM general_messages WHERE deleted_at IS NULL")->fetchColumn();
$packCount     = (int) $db->query("SELECT COUNT(*) FROM packs WHERE deleted_at IS NULL")->fetchColumn();
$promoCount    = (int) $db->query("SELECT COUNT(*) FROM promo_codes WHERE deleted_at IS NULL")->fetchColumn();
$shopProductCount = (int) $db->query("SELECT COUNT(*) FROM shop_products WHERE deleted_at IS NULL")->fetchColumn();
$shopOrderCount   = (int) $db->query("SELECT COUNT(*) FROM shop_orders WHERE status IN ('paid','prepared')")->fetchColumn();

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
        <a href="<?= APP_BASE_URL ?>/admin/messages.php" class="admin-card" style="text-decoration:none;color:inherit">
            <div class="admin-card__icon">📣</div>
            <div class="admin-card__label">Messages</div>
            <div style="color:var(--color-muted);font-size:.9rem;margin-top:.25rem"><?= $messageCount ?> message(s)</div>
        </a>
        <a href="<?= APP_BASE_URL ?>/admin/packs.php" class="admin-card" style="text-decoration:none;color:inherit">
            <div class="admin-card__icon">📦</div>
            <div class="admin-card__label">Packs</div>
            <div style="color:var(--color-muted);font-size:.9rem;margin-top:.25rem"><?= $packCount ?> pack(s)</div>
        </a>
        <a href="<?= APP_BASE_URL ?>/admin/promo-codes.php" class="admin-card" style="text-decoration:none;color:inherit">
            <div class="admin-card__icon">🏷️</div>
            <div class="admin-card__label">Codes promo</div>
            <div style="color:var(--color-muted);font-size:.9rem;margin-top:.25rem"><?= $promoCount ?> code(s)</div>
        </a>
        <a href="<?= APP_BASE_URL ?>/admin/shop-products.php" class="admin-card" style="text-decoration:none;color:inherit">
            <div class="admin-card__icon">🛍️</div>
            <div class="admin-card__label">Boutique – Catalogue</div>
            <div style="color:var(--color-muted);font-size:.9rem;margin-top:.25rem"><?= $shopProductCount ?> produit(s)</div>
        </a>
        <a href="<?= APP_BASE_URL ?>/admin/shop-orders.php" class="admin-card" style="text-decoration:none;color:inherit">
            <div class="admin-card__icon">📦</div>
            <div class="admin-card__label">Boutique – Commandes</div>
            <div style="color:var(--color-muted);font-size:.9rem;margin-top:.25rem"><?= $shopOrderCount ?> commande(s) active(s)</div>
        </a>
        <a href="<?= APP_BASE_URL ?>/admin/shop-market-days.php" class="admin-card" style="text-decoration:none;color:inherit">
            <div class="admin-card__icon">🧺</div>
            <div class="admin-card__label">Boutique – Dates marché</div>
            <div style="color:var(--color-muted);font-size:.9rem;margin-top:.25rem">Gestion des dates candidates</div>
        </a>
    </div>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
