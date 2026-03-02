<?php
// public/admin/session-allowances.php – manage allowed users for a private session
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

$sessionModel = new SessionModel();
$id = isset($_GET['session_id']) ? (int) $_GET['session_id'] : 0;
$session = $id ? $sessionModel->findById($id) : null;

if (!$session) {
    flash('error', 'Séance introuvable.');
    header('Location: ' . APP_BASE_URL . '/admin/sessions.php');
    exit;
}

if (!$session['is_private']) {
    flash('error', 'Cette séance n\'est pas privée.');
    header('Location: ' . APP_BASE_URL . '/admin/sessions.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf();

    $action = $_POST['action'] ?? '';
    $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;

    if ($userId > 0) {
        if ($action === 'allow') {
            $sessionModel->allowUser($id, $userId);
            // Send invitation email
            $userModel = new UserModel();
            $user = $userModel->findById($userId);
            if ($user) {
                Mailer::sendPrivateSessionInvitation($user, $session);
            }
            flash('success', 'Membre autorisé et invitation envoyée.');
        } elseif ($action === 'revoke') {
            // Check if user already has a non-cancelled booking
            $bookingModel = new BookingModel();
            $booking = $bookingModel->findByUserAndSession($userId, $id);
            if ($booking && !in_array($booking['status'], ['cancelled'], true)) {
                flash('error', 'Impossible de révoquer l\'autorisation : ce membre a déjà une réservation.');
            } else {
                $sessionModel->revokeUser($id, $userId);
                flash('success', 'Autorisation révoquée.');
            }
        }
    }

    header('Location: ' . APP_BASE_URL . '/admin/session-allowances.php?session_id=' . $id);
    exit;
}

$users = $sessionModel->getAllowedUsers($id);

$pageTitle = 'Accès – ' . $session['title'];
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:.5rem">
        <h1 class="page-title" style="margin:0">🔒 Accès à la séance privée</h1>
        <a href="<?= APP_BASE_URL ?>/admin/sessions.php" class="btn btn--secondary">← Retour</a>
    </div>

    <div class="flash flash--info" style="margin-bottom:1.5rem">
        <strong><?= e($session['title']) ?></strong> –
        <?= e(formatDate($session['session_date'])) ?>
        <?= e(substr($session['start_time'], 0, 5)) ?> – <?= e(substr($session['end_time'], 0, 5)) ?>
    </div>

    <p>Gérez ici les membres autorisés à s'inscrire à cette séance privée.
       Autoriser un membre lui envoie automatiquement un e-mail d'invitation avec le lien vers la séance.</p>

    <?php if (empty($users)): ?>
        <p>Aucun membre enregistré.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>E-mail</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= e($u['last_name'] . ' ' . $u['first_name']) ?></td>
                            <td><?= e($u['email']) ?></td>
                            <td>
                                <?php if ($u['is_allowed']): ?>
                                    <span class="badge badge--seats-ok">✅ Autorisé</span>
                                    <?php if ($u['has_booking']): ?>
                                        <span class="badge" style="background:#e0e0e0;color:#333">📋 Réservé</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge--seats-full">Non autorisé</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <?php if ($u['is_allowed']): ?>
                                        <?php if (!$u['has_booking']): ?>
                                            <form method="post" action="" onsubmit="return confirm('Révoquer l\'autorisation de ce membre ?')">
                                                <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                                                <input type="hidden" name="action" value="revoke">
                                                <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                                <button type="submit" class="btn btn--warning btn--sm">🚫 Révoquer</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <form method="post" action="">
                                            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                                            <input type="hidden" name="action" value="allow">
                                            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                            <button type="submit" class="btn btn--primary btn--sm">✅ Autoriser</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
