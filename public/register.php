<?php
// public/register.php – user registration
require_once __DIR__ . '/init.php';

if (Auth::isLoggedIn()) {
    header('Location: ' . APP_BASE_URL . '/');
    exit;
}

$errors = [];
$values = ['first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '', 'phone2' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf();

    $values['first_name']   = trim($_POST['first_name']   ?? '');
    $values['last_name']    = trim($_POST['last_name']    ?? '');
    $values['email']        = trim($_POST['email']        ?? '');
    $values['phone']        = trim($_POST['phone']        ?? '');
    $values['phone2']       = trim($_POST['phone2']       ?? '');
    $password               = $_POST['password']          ?? '';
    $passwordConfirm        = $_POST['password_confirm']  ?? '';
    $photoConsent           = isset($_POST['photo_consent']);

    if ($values['first_name'] === '')  $errors[] = 'Le prénom est obligatoire.';
    if ($values['last_name'] === '')   $errors[] = 'Le nom est obligatoire.';
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Adresse e-mail invalide.';
    }
    if (strlen($password) < 8)   $errors[] = 'Le mot de passe doit faire au moins 8 caractères.';
    if ($password !== $passwordConfirm) $errors[] = 'Les mots de passe ne correspondent pas.';

    if (empty($errors)) {
        $userModel = new UserModel();
        if ($userModel->findByEmail($values['email'])) {
            $errors[] = 'Cette adresse e-mail est déjà utilisée.';
        } else {
            $userModel->create([
                'email'         => $values['email'],
                'password'      => $password,
                'first_name'    => $values['first_name'],
                'last_name'     => $values['last_name'],
                'phone'         => $values['phone'] ?: null,
                'phone2'        => $values['phone2'] ?: null,
                'photo_consent' => $photoConsent,
            ]);
            Mailer::sendRegistrationNotificationToAdmin($values);
            flash('success', 'Compte créé ! Vous pouvez maintenant vous connecter.');
            header('Location: ' . APP_BASE_URL . '/login.php');
            exit;
        }
    }
}

$pageTitle = 'Créer un compte';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <div class="form-card">
        <h1>📝 Créer un compte</h1>

        <?php if ($errors): ?>
            <div class="flash flash--error">
                <ul style="margin:0;padding-left:1.2rem">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

            <div class="form-group">
                <label for="first_name">Prénom *</label>
                <input type="text" id="first_name" name="first_name" required value="<?= e($values['first_name']) ?>">
            </div>
            <div class="form-group">
                <label for="last_name">Nom *</label>
                <input type="text" id="last_name" name="last_name" required value="<?= e($values['last_name']) ?>">
            </div>
            <div class="form-group">
                <label for="email">Adresse e-mail *</label>
                <input type="email" id="email" name="email" required value="<?= e($values['email']) ?>">
            </div>
            <div class="form-group">
                <label for="phone">Téléphone du 1er parent</label>
                <input type="tel" id="phone" name="phone" value="<?= e($values['phone']) ?>">
            </div>
            <div class="form-group">
                <label for="phone2">Téléphone du 2e parent</label>
                <input type="tel" id="phone2" name="phone2" value="<?= e($values['phone2']) ?>">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe *</label>
                <input type="password" id="password" name="password" required minlength="8">
                <p class="form-hint">Minimum 8 caractères.</p>
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirmer le mot de passe *</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            <div class="form-group form-group--checkbox">
                <input type="checkbox" id="photo_consent" name="photo_consent">
                <label for="photo_consent">
                    J'autorise l'administrateur à photographier mon enfant et à publier ces photos dans le contenu privé de la séance.
                </label>
            </div>

            <button type="submit" class="btn btn--primary" style="width:100%">Créer mon compte</button>
        </form>

        <p class="mt-2 text-center">Déjà un compte ? <a href="<?= APP_BASE_URL ?>/login.php">Se connecter</a></p>
    </div>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
