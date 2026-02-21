<?php
// public/admin/session-delete.php – soft-delete a session (POST only)
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE_URL . '/admin/sessions.php');
    exit;
}

Auth::verifyCsrf();

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id > 0) {
    $sessionModel = new SessionModel();
    $sessionModel->softDelete($id);
    flash('success', 'Séance supprimée.');
}

header('Location: ' . APP_BASE_URL . '/admin/sessions.php');
exit;
