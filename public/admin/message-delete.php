<?php
// public/admin/message-delete.php – soft-delete a general message (POST only)
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE_URL . '/admin/messages.php');
    exit;
}

Auth::verifyCsrf();

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id > 0) {
    $model = new GeneralMessageModel();
    $model->softDelete($id);
    flash('success', 'Message supprimé.');
}

header('Location: ' . APP_BASE_URL . '/admin/messages.php');
exit;
