<?php
// public/admin/message-edit.php – create or edit a general message
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

$model  = new GeneralMessageModel();
$id     = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$msg    = $id ? $model->findById($id) : null;
$isEdit = (bool) $msg;
$errors = [];

$defaults = ['body' => '', 'type' => 'info'];
$values   = $isEdit ? array_merge($defaults, $msg) : $defaults;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf();

    $values['body'] = trim($_POST['body'] ?? '');
    $values['type'] = trim($_POST['type'] ?? 'info');

    if ($values['body'] === '') {
        $errors[] = 'Le texte du message est obligatoire.';
    }
    if (!in_array($values['type'], GeneralMessageModel::types(), true)) {
        $errors[] = 'Type de message invalide.';
    }

    if (empty($errors)) {
        if ($isEdit) {
            $model->update($id, $values['body'], $values['type']);
            flash('success', 'Message modifié avec succès.');
        } else {
            $model->create($values['body'], $values['type']);
            flash('success', 'Message publié avec succès.');
        }
        header('Location: ' . APP_BASE_URL . '/admin/messages.php');
        exit;
    }
}

$typeLabels = [
    'info'    => '💬 Information',
    'warning' => '⚠️ Avertissement',
    'danger'  => '🚨 Alerte',
    'success' => '✅ Bonne nouvelle',
];

$pageTitle = $isEdit ? 'Modifier le message' : 'Nouveau message';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <h1 class="page-title"><?= $isEdit ? '✏️ Modifier le message' : '➕ Nouveau message' ?></h1>

    <?php if ($errors): ?>
        <div class="flash flash--error">
            <ul style="margin:0;padding-left:1.2rem">
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="" style="max-width:600px">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

        <div class="form-group">
            <label for="type">Type *</label>
            <select id="type" name="type">
                <?php foreach (GeneralMessageModel::types() as $t): ?>
                    <option value="<?= e($t) ?>" <?= $values['type'] === $t ? 'selected' : '' ?>>
                        <?= e($typeLabels[$t]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="body">Message *</label>
            <textarea id="body" name="body" required><?= e($values['body']) ?></textarea>
        </div>

        <div style="display:flex;gap:1rem;margin-top:1.5rem">
            <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Enregistrer' : 'Publier' ?></button>
            <a href="<?= APP_BASE_URL ?>/admin/messages.php" class="btn btn--secondary">Annuler</a>
        </div>
    </form>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
