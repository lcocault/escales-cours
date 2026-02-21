<?php
// public/login.php – user login
require_once __DIR__ . '/init.php';

if (Auth::isLoggedIn()) {
    header('Location: ' . APP_BASE_URL . '/');
    exit;
}

$error  = '';
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf();

    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    $userModel = new UserModel();
    $user = $userModel->verifyPassword($email, $password);

    if ($user) {
        Auth::login($user);
        flash('success', 'Bienvenue, ' . $user['first_name'] . ' !');
        $redirect = $_GET['redirect'] ?? APP_BASE_URL . '/';
        header('Location: ' . $redirect);
        exit;
    } else {
        $error = 'Adresse e-mail ou mot de passe incorrect.';
    }
}

$pageTitle = 'Connexion';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <div class="form-card">
        <h1>🔑 Connexion</h1>

        <?php if ($error): ?>
            <div class="flash flash--error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

            <div class="form-group">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email" required value="<?= e($email) ?>" autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn--primary" style="width:100%">Se connecter</button>
        </form>

        <p class="mt-2 text-center">Pas encore de compte ? <a href="<?= APP_BASE_URL ?>/register.php">S'inscrire</a></p>
    </div>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
