<?php
// public/book.php – booking form (payment redirect via configured provider)
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

if ((int) $session['remaining_seats'] <= 0) {
    flash('error', 'Cette séance est complète.');
    header('Location: ' . APP_BASE_URL . '/session.php?id=' . $sessionId);
    exit;
}

if (strtotime($session['session_date'] . ' ' . $session['end_time']) < time()) {
    flash('error', 'Cette séance est passée.');
    header('Location: ' . APP_BASE_URL . '/session.php?id=' . $sessionId);
    exit;
}

$bookingModel = new BookingModel();
$existing = $bookingModel->findByUserAndSession(Auth::currentUserId(), $sessionId);
if ($existing && in_array($existing['status'], ['confirmed', 'attended', 'pending'])) {
    flash('info', 'Vous avez déjà réservé cette séance.');
    header('Location: ' . APP_BASE_URL . '/my-sessions.php');
    exit;
}

$userModel = new UserModel();
$user = $userModel->findById(Auth::currentUserId());
$useCredit = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf();

    $useCredit = isset($_POST['use_credit']) && (int) $user['credits'] > 0;

    // Create the booking record (status = pending)
    $bookingId = $bookingModel->create(Auth::currentUserId(), $sessionId, $useCredit);

    if ($useCredit) {
        // Free booking via credit
        $bookingModel->confirm($bookingId, 'credit');
        $userModel->updateCredits(Auth::currentUserId(), -1);
        $sessionModel->decrementSeats($sessionId);
        Mailer::sendBookingConfirmationToAttendee($user, $session);
        Mailer::sendBookingNotificationToAdmin($user, $session);
        flash('success', 'Réservation confirmée avec votre crédit !');
        header('Location: ' . APP_BASE_URL . '/my-sessions.php');
        exit;
    }

    // Redirect to payment provider checkout
    try {
        $checkoutUrl = PaymentService::createCheckoutUrl(
            $bookingId,
            $session['title'],
            (int) $session['price_cents'],
            'eur'
        );
    } catch (RuntimeException $e) {
        error_log('PaymentService error: ' . $e->getMessage());
        flash('error', 'Une erreur est survenue lors de la création du paiement. Veuillez réessayer.');
        header('Location: ' . APP_BASE_URL . '/session.php?id=' . $sessionId);
        exit;
    }
    header('Location: ' . $checkoutUrl);
    exit;
}

$pageTitle = 'Réserver – ' . $session['title'];
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <h1 class="page-title">🛒 Réserver une séance</h1>

    <div class="booking-summary">
        <h2><?= e($session['title']) ?></h2>
        <p>📅 <?= e(formatDate($session['session_date'])) ?>
           &nbsp; ⏰ <?= e(substr($session['start_time'], 0, 5)) ?> – <?= e(substr($session['end_time'], 0, 5)) ?>
           &nbsp; 💶 <?= e(formatPrice((int) $session['price_cents'])) ?>
        </p>
    </div>

    <div class="form-card" style="max-width:600px">
        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

            <p><strong>Participant :</strong> <?= e($user['first_name'] . ' ' . $user['last_name']) ?></p>
            <p class="mt-1"><strong>E-mail :</strong> <?= e($user['email']) ?></p>

            <?php if ((int) $user['credits'] > 0): ?>
                <div class="form-group form-group--checkbox mt-2">
                    <input type="checkbox" id="use_credit" name="use_credit" value="1">
                    <label for="use_credit">
                        Utiliser un de mes crédits (<?= (int) $user['credits'] ?> crédit(s) disponible(s)) – réservation gratuite
                    </label>
                </div>
            <?php endif; ?>

            <div class="mt-3">
                <button type="submit" class="btn btn--primary">
                    💳 Procéder au paiement (<?= e(formatPrice((int) $session['price_cents'])) ?>)
                </button>
                <a href="<?= APP_BASE_URL ?>/session.php?id=<?= $sessionId ?>" class="btn btn--secondary" style="margin-left:.5rem">Annuler</a>
            </div>
        </form>
    </div>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
