<?php
// public/about.php – general information page about Escales Culinaires
require_once __DIR__ . '/init.php';

$pageTitle = 'Le concept';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <section class="hero">
        <h1>🍴 Les Escales Culinaires</h1>
        <p>Des ateliers de cuisine pour les petits explorateurs des saveurs !</p>
    </section>

    <!-- Pitch principal -->
    <section class="about-section about-section--highlight">
        <div class="about-section__icon">🧑‍🍳</div>
        <div class="about-section__content">
            <h2>Cuisine, découverte et plaisir partagé</h2>
            <p>
                Les Escales Culinaires, c'est bien plus qu'un atelier de cuisine : c'est un voyage gourmand
                qui mêle apprentissage, curiosité et créativité. Chaque séance amène les enfants à explorer
                un univers culinaire différent, à manipuler de vrais ingrédients et à repartir avec une
                recette réalisée de leurs propres mains — et beaucoup de fierté dans leur tablier !
            </p>
        </div>
    </section>

    <!-- Pour qui -->
    <section class="about-section">
        <div class="about-section__icon">👧👦</div>
        <div class="about-section__content">
            <h2>Pour les enfants de 6 à 12 ans</h2>
            <p>
                Les ateliers sont conçus pour accueillir des enfants de <strong>6 à 12 ans</strong>,
                avec des activités adaptées à leur âge. Que votre enfant soit un futur grand chef ou
                qu'il n'ait jamais tenu une spatule, il trouvera sa place dans une ambiance bienveillante
                et joyeuse. Les groupes sont volontairement petits pour garantir un accompagnement
                personnalisé et un moment convivial.
            </p>
        </div>
    </section>

    <!-- Structure de chaque séance -->
    <section class="about-section">
        <div class="about-section__icon">📋</div>
        <div class="about-section__content">
            <h2>Des séances structurées pour apprendre en s'amusant</h2>
            <p>
                Chaque atelier est organisé en deux temps complémentaires :
            </p>
            <ul class="about-list">
                <li>
                    <span class="about-list__icon">📖</span>
                    <span>
                        <strong>Un temps théorique</strong> : les enfants découvrent l'histoire d'un
                        aliment, comprennent son origine, ses propriétés nutritionnelles, ou apprennent
                        un geste technique (pétrir, émulsionner, ciseler…). De quoi aiguiser leur
                        curiosité et enrichir leur culture culinaire.
                    </span>
                </li>
                <li>
                    <span class="about-list__icon">🥄</span>
                    <span>
                        <strong>Une application pratique</strong> : munis de leur tablier et de leur
                        curiosité, les enfants réalisent la recette du jour étape par étape, en toute
                        autonomie guidée. Le résultat ? Une création dont ils peuvent être fiers — et
                        qu'ils ramèneront à la maison pour la faire goûter !
                    </span>
                </li>
            </ul>
        </div>
    </section>

    <!-- Ce qu'ils vont gagner -->
    <section class="about-section">
        <div class="about-section__icon">🌟</div>
        <div class="about-section__content">
            <h2>Ce que votre enfant va gagner</h2>
            <ul class="about-list about-list--grid">
                <li><span class="about-list__icon">✅</span> Confiance en soi et autonomie</li>
                <li><span class="about-list__icon">✅</span> Curiosité et ouverture culturelle</li>
                <li><span class="about-list__icon">✅</span> Apprentissage de gestes techniques culinaires</li>
                <li><span class="about-list__icon">✅</span> Éducation nutritionnelle ludique</li>
                <li><span class="about-list__icon">✅</span> Esprit d'équipe et partage</li>
                <li><span class="about-list__icon">✅</span> Fierté de cuisiner et de faire goûter</li>
            </ul>
        </div>
    </section>

    <!-- Pourquoi choisir -->
    <section class="about-section about-section--highlight">
        <div class="about-section__icon">💛</div>
        <div class="about-section__content">
            <h2>Pourquoi choisir Les Escales Culinaires ?</h2>
            <ul class="about-list">
                <li>
                    <span class="about-list__icon">🎓</span>
                    <span>Des <strong>intervenants passionnés</strong>, formés à la pédagogie et à la cuisine.</span>
                </li>
                <li>
                    <span class="about-list__icon">🛡️</span>
                    <span><strong>Sécurité et hygiène</strong> au cœur de chaque atelier, avec des ustensiles adaptés aux enfants.</span>
                </li>
                <li>
                    <span class="about-list__icon">🌍</span>
                    <span>Un <strong>voyage culinaire</strong> à travers différentes cuisines du monde à chaque séance.</span>
                </li>
                <li>
                    <span class="about-list__icon">👨‍👩‍👧</span>
                    <span>Des <strong>petits groupes</strong> pour un accompagnement attentionné de chaque enfant.</span>
                </li>
            </ul>
        </div>
    </section>

    <!-- Call to action -->
    <section class="about-cta">
        <h2>Prêt à embarquer ?</h2>
        <p>Consultez les prochaines séances disponibles et réservez dès maintenant la place de votre enfant !</p>
        <div class="about-cta__actions">
            <a href="<?= APP_BASE_URL ?>/" class="btn btn--primary btn--lg">🗓️ Voir les séances</a>
            <?php if (!Auth::isLoggedIn()): ?>
                <a href="<?= APP_BASE_URL ?>/register.php" class="btn btn--secondary btn--lg">✏️ Créer un compte</a>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
