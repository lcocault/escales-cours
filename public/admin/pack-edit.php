<?php
// public/admin/pack-edit.php – create or edit a pack
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

$packModel    = new PackModel();
$sessionModel = new SessionModel();

$id   = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$pack = $id ? $packModel->findById($id) : null;
$isEdit = (bool) $pack;
$errors = [];

// Current session IDs linked to this pack (used to re-check boxes after error)
$linkedSessionIds = $isEdit
    ? array_column($packModel->getSessionsForPack($id), 'id')
    : [];

$defaults = [
    'title'       => '',
    'description' => '',
    'price_cents' => 0,
];
$values = $isEdit ? array_merge($defaults, $pack) : $defaults;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf();

    $values['title']       = trim($_POST['title']       ?? '');
    $values['description'] = trim($_POST['description'] ?? '');
    $values['price_cents'] = (int) round(
        (float) str_replace(',', '.', $_POST['price_euros'] ?? '0') * 100
    );
    $linkedSessionIds = array_map('intval', (array) ($_POST['session_ids'] ?? []));

    if ($values['title'] === '') {
        $errors[] = 'Le titre est obligatoire.';
    }
    if (count($linkedSessionIds) < 2) {
        $errors[] = 'Un pack doit contenir au moins 2 séances.';
    }

    if (empty($errors)) {
        if ($isEdit) {
            $packModel->update($id, $values, $linkedSessionIds);
            flash('success', 'Pack modifié avec succès.');
        } else {
            $packModel->create($values, $linkedSessionIds);
            flash('success', 'Pack créé avec succès.');
        }
        header('Location: ' . APP_BASE_URL . '/admin/packs.php');
        exit;
    }
}

// Load all future (non-cancelled, non-deleted) sessions for the checkbox list
$allSessions = $sessionModel->getAll();

$pageTitle = $isEdit ? 'Modifier le pack' : 'Nouveau pack';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <h1 class="page-title"><?= $isEdit ? '✏️ Modifier le pack' : '➕ Nouveau pack' ?></h1>

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
            <label for="description">Description</label>
            <textarea id="description" name="description"><?= e($values['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="price_euros">Prix global du pack (€) *</label>
            <input type="number" id="price_euros" name="price_euros" required min="0" step="0.01"
                   value="<?= number_format((int) $values['price_cents'] / 100, 2, '.', '') ?>">
        </div>

        <div class="form-group">
            <label>Séances incluses * <span style="font-weight:normal;color:var(--color-muted)">(sélectionnez au moins 2)</span></label>
            <?php if (empty($allSessions)): ?>
                <p style="color:var(--color-muted)">Aucune séance disponible. Créez des séances d'abord.</p>
            <?php else: ?>
                <div style="border:1px solid var(--color-border,#ddd);border-radius:6px;padding:.75rem;max-height:400px;overflow-y:auto">
                    <?php foreach ($allSessions as $s): ?>
                        <?php $checked = in_array((int) $s['id'], $linkedSessionIds, true); ?>
                        <div class="form-group form-group--checkbox" style="margin-bottom:.5rem">
                            <input type="checkbox"
                                   id="session_<?= (int) $s['id'] ?>"
                                   name="session_ids[]"
                                   value="<?= (int) $s['id'] ?>"
                                   <?= $checked ? 'checked' : '' ?>>
                            <label for="session_<?= (int) $s['id'] ?>">
                                <?= e($s['title']) ?>
                                <span style="color:var(--color-muted);font-size:.85rem">
                                    — <?= e(formatDate($s['session_date'])) ?>
                                    <?= e(substr($s['start_time'], 0, 5)) ?>
                                    (<?= (int) $s['remaining_seats'] ?> place(s))
                                    <?php if ($s['status'] === 'cancelled'): ?>
                                        <span class="badge" style="background:#dc2626;color:#fff">Annulée</span>
                                    <?php endif; ?>
                                </span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="display:flex;gap:1rem;margin-top:1.5rem">
            <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Enregistrer' : 'Créer le pack' ?></button>
            <a href="<?= APP_BASE_URL ?>/admin/packs.php" class="btn btn--secondary">Annuler</a>
        </div>
    </form>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
