<?php
// public/admin/session-photo-delete.php – delete a session photo (POST only)
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE_URL . '/admin/sessions.php');
    exit;
}

Auth::verifyCsrf();

$mediaId   = isset($_POST['media_id'])   ? (int) $_POST['media_id']   : 0;
$sessionId = isset($_POST['session_id']) ? (int) $_POST['session_id'] : 0;

if ($mediaId > 0) {
    $db = Database::getInstance();

    // Fetch the record to get the filename (and verify session ownership)
    $stmt = $db->prepare('SELECT * FROM session_media WHERE id = :id' . ($sessionId > 0 ? ' AND session_id = :sid' : ''));
    $params = [':id' => $mediaId];
    if ($sessionId > 0) {
        $params[':sid'] = $sessionId;
    }
    $stmt->execute($params);
    $media = $stmt->fetch();

    if ($media) {
        // Use the media record's own session_id for the redirect target
        $sessionId = (int) $media['session_id'];

        // Only delete physical file for locally-uploaded photos
        if (!empty($media['filename'])) {
            $filePath = ROOT_DIR . '/public/uploads/' . $sessionId . '/' . $media['filename'];
            if (is_file($filePath) && !unlink($filePath)) {
                flash('error', 'Impossible de supprimer le fichier. La photo n\'a pas été supprimée.');
                header('Location: ' . APP_BASE_URL . '/admin/session-photos.php?session_id=' . $sessionId);
                exit;
            }
        }

        // Delete the database record
        $del = $db->prepare('DELETE FROM session_media WHERE id = :id');
        $del->execute([':id' => $mediaId]);

        flash('success', 'Photo supprimée.');
    } else {
        flash('error', 'Photo introuvable.');
    }
}

header('Location: ' . APP_BASE_URL . '/admin/session-photos.php?session_id=' . $sessionId);
exit;
