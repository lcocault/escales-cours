<?php
// public/admin/promo-code-edit.php – create or edit a promotional code
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

$promoModel   = new PromoCodeModel();
$sessionModel = new SessionModel();

$id    = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$promo = $id ? $promoModel->findById($id) : null;
$isEdit = (bool) $promo;
$errors = [];

$defaults = [
    'code'           => '',
    'session_id'     => '',
    'discount_euros' => '',
    'max_uses'       => '',
    'expires_at'     => '',
];

$values = $defaults;
if ($isEdit) {
    $values['code']           = $promo['code'];
    $values['session_id']     = $promo['session_id'] ?? '';
    $values['discount_euros'] = number_format((int) $promo['discount_cents'] / 100, 2, '.', '');
    $values['max_uses']       = $promo['max_uses'] !== null ? (string) (int) $promo['max_uses'] : '';
    $values['expires_at']     = $promo['expires_at']
        ? date('Y-m-d', strtotime($promo['expires_at']))
        : '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf();

    $values['code']           = strtoupper(trim($_POST['code'] ?? ''));
    $values['session_id']     = trim($_POST['session_id'] ?? '');
    $values['discount_euros'] = trim($_POST['discount_euros'] ?? '');
    $values['max_uses']       = trim($_POST['max_uses'] ?? '');
    $values['expires_at']     = trim($_POST['expires_at'] ?? '');

    if ($values['code'] === '') {
        $errors[] = 'Le code est obligatoire.';
    } elseif (!preg_match('/^[A-Z0-9_\-]{2,50}$/', $values['code'])) {
        $errors[] = 'Le code ne peut contenir que des lettres majuscules, chiffres, tirets ou underscores (2–50 caractères).';
    }

    $discountCents = (int) round((float) str_replace(',', '.', $values['discount_euros']) * 100);
    if ($discountCents <= 0) {
        $errors[] = 'La remise doit être supérieure à 0 €.';
    }

    if ($values['max_uses'] !== '' && (!ctype_digit($values['max_uses']) || (int) $values['max_uses'] < 1)) {
        $errors[] = 'Le nombre maximum d\'utilisations doit être un entier positif.';
    }

    if (empty($errors)) {
        $data = [
            'code'           => $values['code'],
            'session_id'     => $values['session_id'] !== '' ? (int) $values['session_id'] : null,
            'discount_cents' => $discountCents,
            'max_uses'       => $values['max_uses'] !== '' ? $values['max_uses'] : null,
            'expires_at'     => $values['expires_at'] !== '' ? $values['expires_at'] : null,
        ];

        if ($isEdit) {
            $promoModel->update($id, $data);
            flash('success', 'Code promotionnel modifié avec succès.');
        } else {
            $promoModel->create($data);
            flash('success', 'Code promotionnel créé avec succès.');
        }
        header('Location: ' . APP_BASE_URL . '/admin/promo-codes.php');
        exit;
    }
}

$allSessions = $sessionModel->getAll();

$pageTitle = $isEdit ? 'Modifier le code promo' : 'Nouveau code promo';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <h1 class="page-title"><?= $isEdit ? '✏️ Modifier le code promo' : '➕ Nouveau code promo' ?></h1>

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
            <label for="code">Code promotionnel *</label>
            <input type="text" id="code" name="code" required
                   value="<?= e($values['code']) ?>"
                   placeholder="ex. : PROMO10"
                   style="text-transform:uppercase"
                   maxlength="50">
            <p class="form-hint">Lettres majuscules, chiffres, tirets ou underscores.</p>
        </div>

        <div class="form-group">
            <label for="discount_euros">Remise (€) *</label>
            <input type="number" id="discount_euros" name="discount_euros" required
                   min="0.01" step="0.01"
                   value="<?= e($values['discount_euros']) ?>">
        </div>

        <div class="form-group">
            <label for="session_id">Séance concernée <span class="optional">(laisser vide pour toutes les séances)</span></label>
            <select id="session_id" name="session_id">
                <option value="">— Toutes les séances —</option>
                <?php foreach ($allSessions as $s): ?>
                    <option value="<?= (int) $s['id'] ?>"
                        <?= (string) $values['session_id'] === (string) $s['id'] ? 'selected' : '' ?>>
                        <?= e($s['title']) ?> — <?= e(formatDate($s['session_date'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="max_uses">Nombre maximum d'utilisations <span class="optional">(laisser vide pour illimité)</span></label>
            <input type="number" id="max_uses" name="max_uses"
                   min="1" step="1"
                   value="<?= e($values['max_uses']) ?>">
        </div>

        <div class="form-group">
            <label for="expires_at">Date d'expiration <span class="optional">(optionnel)</span></label>
            <input type="date" id="expires_at" name="expires_at"
                   value="<?= e($values['expires_at']) ?>">
        </div>

        <div style="display:flex;gap:1rem;margin-top:1.5rem">
            <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Enregistrer' : 'Créer le code' ?></button>
            <a href="<?= APP_BASE_URL ?>/admin/promo-codes.php" class="btn btn--secondary">Annuler</a>
        </div>
    </form>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
