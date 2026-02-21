<?php
// public/admin/session-edit.php – create or edit a session
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

$sessionModel = new SessionModel();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$session = $id ? $sessionModel->findById($id) : null;
$isEdit  = (bool) $session;
$errors  = [];

$defaults = [
    'title'               => '',
    'theme'               => '',
    'session_date'        => '',
    'start_time'          => '',
    'end_time'            => '',
    'max_attendees'       => 10,
    'price_cents'         => 0,
    'summary'             => '',
    'objectives'          => '',
    'theoretical_content' => '',
    'recipe'              => '',
];
$values = $isEdit ? array_merge($defaults, $session) : $defaults;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf();

    $values['title']               = trim($_POST['title']               ?? '');
    $values['theme']               = trim($_POST['theme']               ?? '');
    $values['session_date']        = trim($_POST['session_date']        ?? '');
    $values['start_time']          = trim($_POST['start_time']          ?? '');
    $values['end_time']            = trim($_POST['end_time']            ?? '');
    $values['max_attendees']       = (int) ($_POST['max_attendees']     ?? 0);
    $values['price_cents']         = (int) round((float) str_replace(',', '.', $_POST['price_euros'] ?? '0') * 100);
    $values['summary']             = trim($_POST['summary']             ?? '');
    $values['objectives']          = trim($_POST['objectives']          ?? '');
    $values['theoretical_content'] = trim($_POST['theoretical_content'] ?? '');
    $values['recipe']              = trim($_POST['recipe']              ?? '');

    if ($values['title'] === '')        $errors[] = 'Le titre est obligatoire.';
    if ($values['theme'] === '')        $errors[] = 'Le thème est obligatoire.';
    if ($values['session_date'] === '') $errors[] = 'La date est obligatoire.';
    if ($values['start_time'] === '')   $errors[] = 'L\'heure de début est obligatoire.';
    if ($values['end_time'] === '')     $errors[] = 'L\'heure de fin est obligatoire.';
    if ($values['max_attendees'] < 1)  $errors[] = 'Le nombre maximum de participants doit être ≥ 1.';

    if (empty($errors)) {
        if ($isEdit) {
            $sessionModel->update($id, $values);
            flash('success', 'Séance modifiée avec succès.');
        } else {
            $sessionModel->create($values);
            flash('success', 'Séance créée avec succès.');
        }
        header('Location: ' . APP_BASE_URL . '/admin/sessions.php');
        exit;
    }
}

$pageTitle = $isEdit ? 'Modifier la séance' : 'Nouvelle séance';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <h1 class="page-title"><?= $isEdit ? '✏️ Modifier la séance' : '➕ Nouvelle séance' ?></h1>

    <?php if ($errors): ?>
        <div class="flash flash--error">
            <ul style="margin:0;padding-left:1.2rem">
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="" style="max-width:700px">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

        <div class="form-group">
            <label for="title">Titre *</label>
            <input type="text" id="title" name="title" required value="<?= e($values['title']) ?>">
        </div>
        <div class="form-group">
            <label for="theme">Thème *</label>
            <input type="text" id="theme" name="theme" required value="<?= e($values['theme']) ?>">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
            <div class="form-group">
                <label for="session_date">Date *</label>
                <input type="date" id="session_date" name="session_date" required value="<?= e($values['session_date']) ?>">
            </div>
            <div class="form-group">
                <label for="start_time">Heure début *</label>
                <input type="time" id="start_time" name="start_time" required value="<?= e(substr($values['start_time'], 0, 5)) ?>">
            </div>
            <div class="form-group">
                <label for="end_time">Heure fin *</label>
                <input type="time" id="end_time" name="end_time" required value="<?= e(substr($values['end_time'], 0, 5)) ?>">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div class="form-group">
                <label for="max_attendees">Nombre max de participants *</label>
                <input type="number" id="max_attendees" name="max_attendees" required min="1" value="<?= (int) $values['max_attendees'] ?>">
            </div>
            <div class="form-group">
                <label for="price_euros">Prix (€) *</label>
                <input type="number" id="price_euros" name="price_euros" required min="0" step="0.01"
                       value="<?= number_format((int) $values['price_cents'] / 100, 2, '.', '') ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="summary">Résumé (public)</label>
            <textarea id="summary" name="summary"><?= e($values['summary']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="objectives">Objectifs pédagogiques (après séance)</label>
            <textarea id="objectives" name="objectives"><?= e($values['objectives']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="theoretical_content">Contenu théorique (après séance)</label>
            <textarea id="theoretical_content" name="theoretical_content" style="min-height:180px"><?= e($values['theoretical_content']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="recipe">Recette (après séance)</label>
            <textarea id="recipe" name="recipe" style="min-height:200px"><?= e($values['recipe']) ?></textarea>
        </div>

        <div style="display:flex;gap:1rem;margin-top:1.5rem">
            <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Enregistrer' : 'Créer la séance' ?></button>
            <a href="<?= APP_BASE_URL ?>/admin/sessions.php" class="btn btn--secondary">Annuler</a>
        </div>
    </form>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
