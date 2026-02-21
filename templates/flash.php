<?php
// templates/flash.php – renders session flash messages
Auth::start();
if (!empty($_SESSION['flash'])): ?>
    <div class="flash flash--<?= htmlspecialchars($_SESSION['flash']['type']) ?>">
        <?= htmlspecialchars($_SESSION['flash']['message']) ?>
    </div>
<?php
    unset($_SESSION['flash']);
endif;
