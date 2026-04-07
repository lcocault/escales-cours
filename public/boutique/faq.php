<?php
// public/boutique/faq.php – FAQ for the online shop
require_once __DIR__ . '/../init.php';

$pageTitle = 'Boutique – Questions fréquentes';
$navContext = 'shop';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <section class="hero">
        <h1>❓ Questions fréquentes – Boutique</h1>
        <p>Tout ce que vous souhaitez savoir sur notre boutique en ligne.</p>
    </section>

    <div class="faq-list">

        <details class="faq-item">
            <summary class="faq-item__question">Quand la boutique en ligne sera-t-elle disponible ?</summary>
            <div class="faq-item__answer">
                <p>
                    La boutique en ligne est en cours de préparation et ouvrira très prochainement.
                    Inscrivez-vous sur le site ou suivez-nous sur Instagram pour être informé(e) dès son ouverture !
                </p>
            </div>
        </details>

        <details class="faq-item">
            <summary class="faq-item__question">Quels types de produits seront proposés ?</summary>
            <div class="faq-item__answer">
                <p>La boutique proposera :</p>
                <ul>
                    <li>Des équipements de cuisine adaptés aux enfants (tabliers, ustensiles, outils).</li>
                    <li>Des livres et fiches recettes pour continuer à apprendre à la maison.</li>
                    <li>Des kits de cuisine prêts à l'emploi, parfaits en cadeau.</li>
                    <li>Des coffrets gourmands pour les petits et grands amateurs de cuisine.</li>
                </ul>
            </div>
        </details>

        <details class="faq-item">
            <summary class="faq-item__question">Les produits sont-ils adaptés aux enfants ?</summary>
            <div class="faq-item__answer">
                <p>
                    Oui, absolument. Chaque article est sélectionné avec soin pour sa sécurité, sa qualité
                    et son caractère ludique. Les équipements de cuisine sont spécialement conçus pour les
                    petites mains et respectent les normes de sécurité en vigueur.
                </p>
            </div>
        </details>

        <details class="faq-item">
            <summary class="faq-item__question">Quels seront les modes de livraison ?</summary>
            <div class="faq-item__answer">
                <p>
                    Les détails sur les modes de livraison (livraison à domicile, retrait en boutique à Toulouse,
                    etc.) seront précisés lors de l'ouverture de la boutique. Restez connecté(e) pour les
                    premières annonces !
                </p>
            </div>
        </details>

        <details class="faq-item">
            <summary class="faq-item__question">Les prix sont-ils TTC ?</summary>
            <div class="faq-item__answer">
                <p>
                    Oui, tous les prix affichés sur le site sont en euros <strong>TTC</strong>
                    (art.&nbsp;293&nbsp;B du CGI). Les Escales Culinaires relèvent du régime des
                    auto-entrepreneurs.
                </p>
            </div>
        </details>

        <details class="faq-item">
            <summary class="faq-item__question">Comment effectuer un retour ou un remboursement ?</summary>
            <div class="faq-item__answer">
                <p>
                    Les conditions de retour et de remboursement seront précisées lors de l'ouverture de la
                    boutique. Pour toute question, n'hésitez pas à nous contacter directement.
                </p>
            </div>
        </details>

        <details class="faq-item">
            <summary class="faq-item__question">Comment nous contacter ?</summary>
            <div class="faq-item__answer">
                <p>
                    📞 <a href="tel:+33650071091">06 50 07 10 91</a><br>
                    ✉️ <a href="mailto:les.escales.culinaires@gmail.com">les.escales.culinaires@gmail.com</a><br>
                    <a href="https://www.instagram.com/les.escales.culinaires" target="_blank" rel="noopener noreferrer" class="instagram-link">
                        <?php include ROOT_DIR . '/templates/instagram-icon.php'; ?>
                        @les.escales.culinaires
                    </a>
                </p>
                <p>
                    Responsable : <strong>Emmanuelle Du Puy De Goyne</strong>
                </p>
            </div>
        </details>

    </div>

    <div class="about-cta mt-3">
        <h2>Une autre question ?</h2>
        <p>Contactez-nous, nous serons ravis de vous répondre !</p>
        <div class="about-cta__actions">
            <a href="mailto:les.escales.culinaires@gmail.com" class="btn btn--primary btn--lg">✉️ Nous écrire</a>
            <a href="<?= APP_BASE_URL ?>/ateliers/" class="btn btn--secondary btn--lg">🍳 Voir les ateliers</a>
        </div>
    </div>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
