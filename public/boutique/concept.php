<?php
// public/boutique/concept.php – concept page for the online shop
require_once __DIR__ . '/../init.php';

$pageTitle = 'La boutique – Le concept';
$navContext = 'shop';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <section class="hero">
        <h1>🛍️ La Boutique Escales Culinaires</h1>
        <p>Des produits gourmands et des équipements pour cuisiner en famille !</p>
    </section>

    <!-- Pitch principal -->
    <section class="about-section about-section--highlight">
        <div class="about-section__icon">🌟</div>
        <div class="about-section__content">
            <h2>Prolongez l'aventure culinaire à la maison</h2>
            <p>
                La boutique des Escales Culinaires, c'est l'extension naturelle de nos ateliers : une
                sélection soigneuse de produits et d'équipements pour que vos enfants puissent continuer
                à explorer les plaisirs de la cuisine dans leur propre cuisine. Chaque article est choisi
                pour sa qualité, sa sécurité et son caractère ludique.
            </p>
        </div>
    </section>

    <!-- Ce que vous trouverez -->
    <section class="about-section">
        <div class="about-section__icon">🎁</div>
        <div class="about-section__content">
            <h2>Ce que vous trouverez dans la boutique</h2>
            <ul class="about-list">
                <li>
                    <span class="about-list__icon">👨‍🍳</span>
                    <span><strong>Équipements de cuisine</strong> : tabliers, ustensiles et outils adaptés aux petites mains.</span>
                </li>
                <li>
                    <span class="about-list__icon">📚</span>
                    <span><strong>Livres et fiches recettes</strong> : des recettes accessibles pour continuer à apprendre en s'amusant.</span>
                </li>
                <li>
                    <span class="about-list__icon">🧑‍🍳</span>
                    <span><strong>Kits de cuisine</strong> : tout le nécessaire pour réaliser une recette de A à Z, idéal en cadeau.</span>
                </li>
                <li>
                    <span class="about-list__icon">🎀</span>
                    <span><strong>Idées cadeaux</strong> : des coffrets gourmands pour les petits et grands amateurs de cuisine.</span>
                </li>
            </ul>
        </div>
    </section>

    <!-- Nos engagements -->
    <section class="about-section about-section--highlight">
        <div class="about-section__icon">💛</div>
        <div class="about-section__content">
            <h2>Nos engagements</h2>
            <ul class="about-list">
                <li>
                    <span class="about-list__icon">🛡️</span>
                    <span><strong>Sécurité</strong> : des produits testés et approuvés, adaptés aux enfants.</span>
                </li>
                <li>
                    <span class="about-list__icon">🌱</span>
                    <span><strong>Qualité</strong> : une sélection rigoureuse de produits durables et respectueux de l'environnement.</span>
                </li>
                <li>
                    <span class="about-list__icon">❤️</span>
                    <span><strong>Passion</strong> : chaque article est choisi avec soin pour enrichir l'expérience culinaire de vos enfants.</span>
                </li>
                <li>
                    <span class="about-list__icon">🤝</span>
                    <span><strong>Proximité</strong> : une boutique locale, à Toulouse, avec un service personnalisé.</span>
                </li>
            </ul>
        </div>
    </section>

    <!-- Où nous trouver -->
    <section class="about-section">
        <div class="about-section__icon">📍</div>
        <div class="about-section__content">
            <h2>Nous retrouver</h2>
            <address class="about-address">
                <strong>Les Escales Culinaires</strong><br>
                36 rue Boieldieu<br>
                31300 Toulouse<br>
                France
            </address>
            <p class="mt-1">
                📞 <a href="tel:+33650071091">06 50 07 10 91</a><br>
                ✉️ <a href="mailto:les.escales.culinaires@gmail.com">les.escales.culinaires@gmail.com</a>
            </p>
            <p class="mt-1 about-owner">
                Les Escales Culinaires sont animées par <strong>Emmanuelle Du Puy De Goyne</strong>,
                auto-entrepreneur. Les prix indiqués sur le site sont en euros TTC
                (art.&nbsp;293&nbsp;B du CGI).
            </p>
        </div>
    </section>

    <!-- Coming soon -->
    <section class="about-cta">
        <h2>Bientôt disponible !</h2>
        <p>La boutique en ligne ouvre très prochainement. En attendant, découvrez nos ateliers de cuisine pour enfants !</p>
        <div class="about-cta__actions">
            <a href="<?= APP_BASE_URL ?>/ateliers/" class="btn btn--primary btn--lg">🍳 Voir les ateliers</a>
            <a href="<?= APP_BASE_URL ?>/boutique/faq.php" class="btn btn--secondary btn--lg">❓ Questions fréquentes</a>
        </div>
    </section>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
