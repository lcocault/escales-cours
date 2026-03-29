<?php
// public/admin/pack-delete.php – soft-delete a pack
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE_URL . '/admin/packs.php');
    exit;
}

Auth::verifyCsrf();

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$packModel = new PackModel();
$pack = $packModel->findById($id);

if (!$pack) {
    flash('error', 'Pack introuvable.');
    header('Location: ' . APP_BASE_URL . '/admin/packs.php');
    exit;
}

$packModel->delete($id);
flash('success', 'Pack supprimé.');
header('Location: ' . APP_BASE_URL . '/admin/packs.php');
exit;
