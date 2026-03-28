<?php
// public/admin/session-photos.php – manage photos for a session
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

$sessionModel = new SessionModel();
$sessionId = isset($_GET['session_id']) ? (int) $_GET['session_id'] : 0;
$session = $sessionId ? $sessionModel->findById($sessionId) : null;

if (!$session) {
    flash('error', 'Séance introuvable.');
    header('Location: ' . APP_BASE_URL . '/admin/sessions.php');
    exit;
}

$db = Database::getInstance();
$errors = [];

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    Auth::verifyCsrf();

    $isPrivate = isset($_POST['is_private']);
    $uploadDir = ROOT_DIR . '/public/uploads/' . $sessionId . '/';

    if (empty($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Veuillez sélectionner un fichier.';
    } elseif ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Erreur lors du téléchargement du fichier (code ' . $_FILES['photo']['error'] . ').';
    } else {
        $file = $_FILES['photo'];
        $maxSize = 8 * 1024 * 1024; // 8 MB

        if ($file['size'] > $maxSize) {
            $errors[] = 'Le fichier est trop volumineux (8 Mo maximum).';
        } else {
            // Validate MIME type using finfo
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            if ($mime === false) {
                $errors[] = 'Impossible de vérifier le type du fichier.';
            } else {
                $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

                if (!array_key_exists($mime, $allowedMimes)) {
                    $errors[] = 'Type de fichier non autorisé. Seuls JPEG, PNG et WebP sont acceptés.';
                } else {
                    // Create upload directory if needed
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $ext = $allowedMimes[$mime];
                    // Generate unique filename (loop to avoid collisions, however unlikely)
                    do {
                        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
                        $destination = $uploadDir . $filename;
                    } while (file_exists($destination));

                    if (!move_uploaded_file($file['tmp_name'], $destination)) {
                        $errors[] = 'Impossible de sauvegarder le fichier. Vérifiez les permissions du répertoire.';
                    } else {
                        $stmt = $db->prepare(
                            'INSERT INTO session_media (session_id, filename, is_private) VALUES (:sid, :filename, :private)'
                        );
                        $stmt->execute([
                            ':sid'      => $sessionId,
                            ':filename' => $filename,
                            ':private'  => $isPrivate ? 'true' : 'false',
                        ]);
                        flash('success', 'Photo ajoutée avec succès.');
                        header('Location: ' . APP_BASE_URL . '/admin/session-photos.php?session_id=' . $sessionId);
                        exit;
                    }
                }
            }
        }
    }
}

// Load existing media
$stmt = $db->prepare('SELECT * FROM session_media WHERE session_id = :sid ORDER BY created_at ASC');
$stmt->execute([':sid' => $sessionId]);
$mediaList = $stmt->fetchAll();

$pageTitle = 'Photos – ' . $session['title'];
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <h1 class="page-title">📸 Photos : <?= e($session['title']) ?></h1>
    <p style="color:var(--color-muted);margin-bottom:1.5rem">
        📅 <?= e(formatDate($session['session_date'])) ?>
    </p>

    <?php if ($errors): ?>
        <div class="flash flash--error">
            <ul style="margin:0;padding-left:1.2rem">
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Upload form -->
    <div class="section-block" style="max-width:520px;margin-bottom:2rem">
        <h2 style="margin-bottom:1rem">➕ Ajouter une photo</h2>
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
            <input type="hidden" name="action" value="upload">

            <div class="form-group">
                <label for="photo">Fichier (JPEG, PNG ou WebP, 8 Mo max) *</label>
                <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" required>
            </div>
            <div class="form-group form-group--checkbox">
                <input type="checkbox" id="is_private" name="is_private" value="1" checked>
                <label for="is_private">🔒 Photo privée (réservée aux parents avec consentement photo)</label>
            </div>
            <button type="submit" class="btn btn--primary">Envoyer</button>
        </form>
    </div>

    <!-- Existing photos -->
    <h2 style="margin-bottom:1rem">Photos existantes (<?= count($mediaList) ?>)</h2>

    <?php if (empty($mediaList)): ?>
        <p style="color:var(--color-muted)">Aucune photo pour cette séance.</p>
    <?php else: ?>
        <div style="display:flex;flex-wrap:wrap;gap:1.5rem">
            <?php foreach ($mediaList as $media): ?>
                <div style="border:1px solid var(--color-border);border-radius:var(--radius);padding:.75rem;background:#fff;max-width:220px">
                    <img src="<?= APP_BASE_URL ?>/uploads/<?= e($sessionId . '/' . $media['filename']) ?>"
                         alt="Photo de la séance"
                         style="width:200px;height:150px;object-fit:cover;border-radius:8px;display:block;margin-bottom:.5rem">
                    <p style="font-size:.85rem;color:var(--color-muted);margin-bottom:.5rem">
                        <?= $media['is_private'] ? '🔒 Privée' : '🌐 Publique' ?>
                    </p>
                    <form method="post" action="<?= APP_BASE_URL ?>/admin/session-photo-delete.php"
                          onsubmit="return confirm('Supprimer cette photo ?')">
                        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                        <input type="hidden" name="media_id" value="<?= (int) $media['id'] ?>">
                        <input type="hidden" name="session_id" value="<?= (int) $sessionId ?>">
                        <button type="submit" class="btn btn--danger btn--sm">🗑️ Supprimer</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p class="mt-3">
        <a href="<?= APP_BASE_URL ?>/admin/sessions.php">← Retour aux séances</a>
    </p>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
