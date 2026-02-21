<?php
// public/session-content.php – detailed post-session content for confirmed attendees
require_once __DIR__ . '/init.php';
Auth::requireLogin();

$sessionId = isset($_GET['session_id']) ? (int) $_GET['session_id'] : 0;
$sessionModel = new SessionModel();
$session = $sessionModel->findById($sessionId);

if (!$session) {
    flash('error', 'Séance introuvable.');
    header('Location: ' . APP_BASE_URL . '/');
    exit;
}

$bookingModel = new BookingModel();
if (!$bookingModel->hasAccessToContent(Auth::currentUserId(), $sessionId) && !Auth::isAdmin()) {
    flash('error', 'Accès refusé. Vous devez avoir participé à cette séance.');
    header('Location: ' . APP_BASE_URL . '/session.php?id=' . $sessionId);
    exit;
}

// Load user to check photo_consent
$userModel = new UserModel();
$user = $userModel->findById(Auth::currentUserId());

// Load session media
$db = Database::getInstance();
$stmt = $db->prepare(
    'SELECT * FROM session_media WHERE session_id = :sid ORDER BY created_at ASC'
);
$stmt->execute([':sid' => $sessionId]);
$mediaList = $stmt->fetchAll();

$pageTitle = 'Contenu – ' . $session['title'];
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <article class="session-detail">
        <header class="session-detail__header">
            <h1 class="session-detail__title">📚 <?= e($session['title']) ?></h1>
            <p class="session-detail__meta">📅 <?= e(formatDate($session['session_date'])) ?></p>
        </header>

        <?php if ($session['objectives']): ?>
            <section class="section-block">
                <h2>🎯 Objectifs pédagogiques</h2>
                <p><?= nl2br(e($session['objectives'])) ?></p>
            </section>
        <?php endif; ?>

        <?php if ($session['theoretical_content']): ?>
            <section class="section-block">
                <h2>📖 Contenu théorique</h2>
                <p><?= nl2br(e($session['theoretical_content'])) ?></p>
            </section>
        <?php endif; ?>

        <?php if ($session['recipe']): ?>
            <section class="section-block">
                <h2>👨‍🍳 La recette</h2>
                <p><?= nl2br(e($session['recipe'])) ?></p>
            </section>
        <?php endif; ?>

        <!-- Photos -->
        <?php
        $publicMedia  = array_filter($mediaList, fn($m) => !$m['is_private']);
        $privateMedia = array_filter($mediaList, fn($m) => (bool) $m['is_private']);
        ?>
        <?php if ($publicMedia): ?>
            <section class="section-block">
                <h2>📸 Photos de la séance</h2>
                <div style="display:flex;flex-wrap:wrap;gap:1rem;margin-top:.75rem">
                    <?php foreach ($publicMedia as $media): ?>
                        <img src="<?= APP_BASE_URL ?>/uploads/<?= e($session['id'] . '/' . $media['filename']) ?>"
                             alt="Photo de la séance"
                             style="width:200px;height:150px;object-fit:cover;border-radius:8px">
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($privateMedia): ?>
            <section class="section-block">
                <h2>🔒 Photos privées (enfants)</h2>
                <?php if ($user['photo_consent']): ?>
                    <div style="display:flex;flex-wrap:wrap;gap:1rem;margin-top:.75rem">
                        <?php foreach ($privateMedia as $media): ?>
                            <img src="<?= APP_BASE_URL ?>/uploads/<?= e($session['id'] . '/' . $media['filename']) ?>"
                                 alt="Photo privée de la séance"
                                 style="width:200px;height:150px;object-fit:cover;border-radius:8px">
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="locked-content">
                        <p>📸 Ces photos incluent des enfants. Vous n'avez pas autorisé la publication de photos de votre enfant lors de votre inscription.</p>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </article>

    <p class="mt-2"><a href="<?= APP_BASE_URL ?>/my-sessions.php">← Retour à mes réservations</a></p>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
