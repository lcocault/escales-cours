<?php
// public/index.php – portal homepage: learning sessions & shop
require_once __DIR__ . '/init.php';

$pageTitle       = 'Accueil';
$messageModel    = new GeneralMessageModel();
$latestMessage   = $messageModel->getLatest();
$hasMoreMessages = $messageModel->countAll() > 1;

include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <section class="hero">
        <h1>🍳 Les Escales Culinaires</h1>
        <p>Ateliers de cuisine &amp; boutique gourmande à Toulouse</p>
        <p class="hero__location">📍 36 rue Boieldieu, 31300 Toulouse</p>
    </section>

    <?php if ($latestMessage !== null): ?>
        <section class="news-thread" aria-label="Actualités">
            <div class="news-item news-item--<?= e($latestMessage['type']) ?>">
                <span class="news-item__icon" aria-hidden="true"><?= [
                    'info'    => '💬',
                    'warning' => '⚠️',
                    'danger'  => '🚨',
                    'success' => '✅',
                ][$latestMessage['type']] ?? '📢' ?></span>
                <div class="news-item__body">
                    <p class="news-item__date"><?= e(date('d/m/Y', strtotime($latestMessage['created_at']))) ?></p>
                    <p class="news-item__text"><?= e($latestMessage['body']) ?></p>
                </div>
            </div>
            <?php if ($hasMoreMessages): ?>
                <p class="news-thread__more">
                    <a href="<?= APP_BASE_URL ?>/messages.php">📋 Voir tous les messages →</a>
                </p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <div class="portal-grid">
        <a href="<?= APP_BASE_URL ?>/sessions.php" class="portal-card portal-card--sessions">
            <span class="portal-card__icon">🍳</span>
            <h2 class="portal-card__title">Ateliers de cuisine</h2>
            <p class="portal-card__desc">Des cours de cuisine ludiques et pédagogiques pour les enfants. Réservez votre séance en ligne !</p>
            <span class="btn btn--primary portal-card__cta">Voir les séances →</span>
        </a>

        <div class="portal-card portal-card--shop portal-card--coming-soon">
            <span class="portal-card__icon">🛍️</span>
            <h2 class="portal-card__title">Boutique</h2>
            <p class="portal-card__desc">Retrouvez bientôt nos produits et équipements pour cuisiner en famille !</p>
            <span class="badge badge--coming-soon">Bientôt disponible</span>
        </div>
    </div>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
