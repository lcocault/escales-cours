<?php
// public/admin/promo-code-delete.php – soft-delete a promotional code
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();
Auth::verifyCsrf();

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id > 0) {
    $promoModel = new PromoCodeModel();
    $promoModel->delete($id);
    flash('success', 'Code promotionnel supprimé.');
} else {
    flash('error', 'Code introuvable.');
}

header('Location: ' . APP_BASE_URL . '/admin/promo-codes.php');
exit;
