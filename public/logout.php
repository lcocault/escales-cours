<?php
// public/logout.php – logout action (POST only)
require_once __DIR__ . '/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf();
    Auth::logout();
    flash('success', 'Vous êtes maintenant déconnecté·e.');
}

header('Location: ' . APP_BASE_URL . '/');
exit;
