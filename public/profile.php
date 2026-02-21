<?php
// public/profile.php – user account page
require_once __DIR__ . '/init.php';
Auth::requireLogin();

$userModel = new UserModel();
$user = $userModel->findById(Auth::currentUserId());

$pageTitle = 'Mon compte';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <h1 class="page-title">👤 Mon compte</h1>

    <div class="session-detail">
        <p><strong>Prénom :</strong> <?= e($user['first_name']) ?></p>
        <p><strong>Nom :</strong> <?= e($user['last_name']) ?></p>
        <p><strong>E-mail :</strong> <?= e($user['email']) ?></p>
        <p><strong>Téléphone :</strong> <?= e($user['phone'] ?? '–') ?></p>
        <p><strong>Autorisation photos :</strong> <?= $user['photo_consent'] ? '✅ Oui' : '❌ Non' ?></p>
        <p><strong>Crédits disponibles :</strong> <?= (int) $user['credits'] ?></p>

        <div class="mt-3">
            <a href="<?= APP_BASE_URL ?>/my-sessions.php" class="btn btn--secondary">Mes réservations →</a>
        </div>
    </div>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
