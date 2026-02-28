<?php
// templates/footer.php
?>
</main>
<footer class="site-footer">
    <div class="container">
        <div class="site-footer__grid">
            <div class="site-footer__col">
                <p class="site-footer__brand">🍳 Les Escales Culinaires</p>
                <address class="site-footer__address">
                    36 rue Boieldieu<br>
                    31300 Toulouse<br>
                    France
                </address>
            </div>
            <div class="site-footer__col">
                <p>📞 <a href="tel:+33650071091">06 50 07 10 91</a></p>
                <p>✉️ <a href="mailto:les.escales.culinaires@gmail.com">les.escales.culinaires@gmail.com</a></p>
            </div>
            <div class="site-footer__col">
                <nav aria-label="Liens utiles">
                    <ul class="site-footer__links">
                        <li><a href="<?= APP_BASE_URL ?>/about.php">Le concept</a></li>
                        <li><a href="<?= APP_BASE_URL ?>/faq.php">FAQ</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <p class="site-footer__legal">
            © <?= date('Y') ?> Les Escales Culinaires – Emmanuelle Du Puy De Goyne.
            Les prix sont indiqués en euros TTC (art. 293 B du CGI).
            Régime auto-entrepreneur.
        </p>
    </div>
</footer>
</body>
</html>
