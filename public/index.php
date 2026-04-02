<?php
// public/index.php – portal homepage: learning sessions & shop
require_once __DIR__ . '/init.php';

$pageTitle = 'Accueil';

include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <section class="hero">
        <h1>🍳 Les Escales Culinaires</h1>
        <p>Ateliers de cuisine &amp; boutique gourmande à Toulouse</p>
        <p class="hero__location">📍 36 rue Boieldieu, 31300 Toulouse</p>
    </section>

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
