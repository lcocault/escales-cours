<?php
// templates/header.php
// $pageTitle should be set before including this file
$pageTitle = $pageTitle ?? 'Escales Culinaires';
Auth::start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> – Escales Culinaires</title>
    <link rel="stylesheet" href="<?= APP_BASE_URL ?>/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="site-header__inner container">
        <a class="site-header__logo" href="<?= APP_BASE_URL ?>/">
            <span class="logo-icon" aria-hidden="true">🍳</span>
            <span class="logo-text">Escales Culinaires</span>
        </a>
        <nav class="site-nav" aria-label="Navigation principale">
            <ul class="site-nav__list">
                <li><a href="<?= APP_BASE_URL ?>/">Séances</a></li>
                <li><a href="<?= APP_BASE_URL ?>/about.php">Le concept</a></li>
                <li><a href="<?= APP_BASE_URL ?>/faq.php">FAQ</a></li>
                <?php if (Auth::isLoggedIn()): ?>
                    <li><a href="<?= APP_BASE_URL ?>/my-sessions.php">Mes réservations</a></li>
                    <?php if (Auth::isAdmin()): ?>
                        <li><a href="<?= APP_BASE_URL ?>/admin/">Administration</a></li>
                    <?php endif; ?>
                    <li><a href="<?= APP_BASE_URL ?>/profile.php">Mon compte</a></li>
                    <li>
                        <form method="post" action="<?= APP_BASE_URL ?>/logout.php" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                            <button type="submit" class="btn btn--link">Déconnexion</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li><a href="<?= APP_BASE_URL ?>/login.php">Connexion</a></li>
                    <li><a href="<?= APP_BASE_URL ?>/register.php" class="btn btn--primary">S'inscrire</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<main class="site-main">
