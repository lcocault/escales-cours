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

// Handle local photo upload
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

// Handle external URL photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_url') {
    Auth::verifyCsrf();

    $isPrivate   = isset($_POST['is_private']);
    $externalUrl = trim($_POST['external_url'] ?? '');

    if ($externalUrl === '') {
        $errors[] = 'Veuillez saisir une URL.';
    } elseif (!filter_var($externalUrl, FILTER_VALIDATE_URL)) {
        $errors[] = 'L\'URL saisie n\'est pas valide.';
    } elseif (!preg_match('#^https?://#i', $externalUrl)) {
        $errors[] = 'L\'URL doit commencer par http:// ou https://.';
    } else {
        $stmt = $db->prepare(
            'INSERT INTO session_media (session_id, external_url, is_private) VALUES (:sid, :url, :private)'
        );
        $stmt->execute([
            ':sid'     => $sessionId,
            ':url'     => $externalUrl,
            ':private' => $isPrivate ? 'true' : 'false',
        ]);
        flash('success', 'Photo (URL externe) ajoutée avec succès.');
        header('Location: ' . APP_BASE_URL . '/admin/session-photos.php?session_id=' . $sessionId);
        exit;
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

        <!-- Tab toggle -->
        <div style="display:flex;gap:.5rem;margin-bottom:1rem">
            <button type="button" id="tab-upload" class="btn btn--secondary btn--sm"
                    onclick="showTab('upload')" style="font-weight:bold">📁 Fichier local</button>
            <button type="button" id="tab-url" class="btn btn--secondary btn--sm"
                    onclick="showTab('url')">🔗 URL externe</button>
        </div>

        <!-- Local file upload -->
        <div id="panel-upload">
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

        <!-- External URL -->
        <div id="panel-url" style="display:none">
            <form method="post" action="">
                <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                <input type="hidden" name="action" value="add_url">

                <div class="form-group">
                    <label for="external_url">URL publique de la photo *</label>
                    <input type="url" id="external_url" name="external_url"
                           placeholder="https://example.com/photo.jpg"
                           style="width:100%;box-sizing:border-box" required>
                </div>
                <div class="form-group form-group--checkbox">
                    <input type="checkbox" id="is_private_url" name="is_private" value="1" checked>
                    <label for="is_private_url">🔒 Photo privée (réservée aux parents avec consentement photo)</label>
                </div>
                <button type="submit" class="btn btn--primary">Ajouter</button>
            </form>
        </div>

        <script>
        function showTab(tab) {
            document.getElementById('panel-upload').style.display = tab === 'upload' ? '' : 'none';
            document.getElementById('panel-url').style.display   = tab === 'url'    ? '' : 'none';
            document.getElementById('tab-upload').style.fontWeight = tab === 'upload' ? 'bold' : '';
            document.getElementById('tab-url').style.fontWeight    = tab === 'url'    ? 'bold' : '';
        }
        <?php if (isset($_POST['action']) && $_POST['action'] === 'add_url'): ?>
        showTab('url');
        <?php endif; ?>
        </script>
    </div>

    <!-- Existing photos -->
    <h2 style="margin-bottom:1rem">Photos existantes (<?= count($mediaList) ?>)</h2>

    <?php if (empty($mediaList)): ?>
        <p style="color:var(--color-muted)">Aucune photo pour cette séance.</p>
    <?php else: ?>
        <div style="display:flex;flex-wrap:wrap;gap:1.5rem">
            <?php foreach ($mediaList as $media): ?>
                <?php
                $imgSrc = !empty($media['external_url'])
                    ? $media['external_url']
                    : APP_BASE_URL . '/uploads/' . e($sessionId) . '/' . e($media['filename']);
                ?>
                <div style="border:1px solid var(--color-border);border-radius:var(--radius);padding:.75rem;background:#fff;max-width:220px">
                    <img src="<?= e($imgSrc) ?>"
                         alt="Photo de la séance"
                         style="width:200px;height:150px;object-fit:cover;border-radius:8px;display:block;margin-bottom:.5rem">
                    <p style="font-size:.85rem;color:var(--color-muted);margin-bottom:.5rem">
                        <?= pgBool($media['is_private']) ? '🔒 Privée' : '🌐 Publique' ?>
                        <?= !empty($media['external_url']) ? ' · 🔗 URL' : '' ?>
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
