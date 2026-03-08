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

if (!empty($session['is_private'])) {
    if (!Auth::isAdmin() && !$sessionModel->isUserAllowed($sessionId, Auth::currentUserId())) {
        flash('error', 'Vous n\'êtes pas autorisé(e) à réserver cette séance privée.');
        header('Location: ' . APP_BASE_URL . '/session.php?id=' . $sessionId);
        exit;
    }
}

if (strtotime($session['session_date'] . ' ' . $session['end_time']) < time()) {
    flash('error', 'Cette séance est passée.');
    header('Location: ' . APP_BASE_URL . '/session.php?id=' . $sessionId);
    exit;
}

$bookingModel = new BookingModel();
$existing = $bookingModel->findByUserAndSession(Auth::currentUserId(), $sessionId);
if ($existing && in_array($existing['status'], ['confirmed', 'attended'])) {
    flash('info', 'Vous avez déjà réservé cette séance.');
    header('Location: ' . APP_BASE_URL . '/my-sessions.php');
    exit;
}
if ($existing && $existing['status'] === 'pending') {
    // Resume payment for the existing pending booking
    try {
        $checkout = PaymentService::createCheckoutUrl(
            (int) $existing['id'],
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
    if (!empty($checkout['squareOrderId'])) {
        $bookingModel->storePaymentRef((int) $existing['id'], 'sq_order_' . $checkout['squareOrderId']);
    }
    header('Location: ' . $checkout['url']);
    exit;
}

$userModel = new UserModel();
$user = $userModel->findById(Auth::currentUserId());
$useCredit = false;
$errors = [];
$childFirstName = '';
$childLastName  = $user['last_name'] ?? '';
$childAge       = '';
$childAllergies = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf();

    $childFirstName = trim($_POST['child_first_name'] ?? '');
    $childLastName  = trim($_POST['child_last_name']  ?? '');
    $childAge       = trim($_POST['child_age']        ?? '');
    $childAllergies = trim($_POST['child_allergies']  ?? '');

    if ($childFirstName === '') {
        $errors[] = 'Le prénom de l\'enfant est obligatoire.';
    }
    if ($childLastName === '') {
        $errors[] = 'Le nom de l\'enfant est obligatoire.';
    }
    if ($childAge === '' || !ctype_digit($childAge) || (int) $childAge < 1 || (int) $childAge > 17) {
        $errors[] = 'L\'âge de l\'enfant doit être un nombre entier entre 1 et 17.';
    }

    if (empty($errors)) {
        $useCredit = isset($_POST['use_credit']) && (int) $user['credits'] > 0;
        $action    = $_POST['action'] ?? 'pay';

        if ($action === 'basket') {
            // Add to basket and redirect to basket page
            $basketModel = new BasketModel();
            $basketModel->addItem(
                Auth::currentUserId(),
                $sessionId,
                $childFirstName,
                $childLastName,
                (int) $childAge,
                $childAllergies
            );
            flash('success', '🛒 Séance ajoutée au panier !');
            header('Location: ' . APP_BASE_URL . '/basket.php');
            exit;
        }

        // Create the booking record (status = pending)
        $bookingId = $bookingModel->create(
            Auth::currentUserId(),
            $sessionId,
            $useCredit,
            $childFirstName,
            $childLastName,
            (int) $childAge,
            $childAllergies
        );

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
            $checkout = PaymentService::createCheckoutUrl(
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

        // Store the Square order ID before redirecting so we can refund later if needed.
        if (!empty($checkout['squareOrderId'])) {
            $bookingModel->storePaymentRef($bookingId, 'sq_order_' . $checkout['squareOrderId']);
        }

        header('Location: ' . $checkout['url']);
        exit;
    }
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
        <?php if (!empty($errors)): ?>
            <ul class="alert alert--error">
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

            <p><strong>Réservé par :</strong> <?= e($user['first_name'] . ' ' . $user['last_name']) ?></p>
            <p class="mt-1"><strong>E-mail :</strong> <?= e($user['email']) ?></p>

            <hr class="mt-2 mb-2">
            <h3 class="form-section-title">Informations sur l'enfant</h3>

            <div class="form-group mt-1">
                <label for="child_first_name">Prénom de l'enfant <span class="required">*</span></label>
                <input type="text" id="child_first_name" name="child_first_name"
                       value="<?= e($childFirstName) ?>" required>
            </div>

            <div class="form-group mt-1">
                <label for="child_last_name">Nom de l'enfant <span class="required">*</span></label>
                <input type="text" id="child_last_name" name="child_last_name"
                       value="<?= e($childLastName) ?>" required>
            </div>

            <div class="form-group mt-1">
                <label for="child_age">Âge de l'enfant <span class="required">*</span></label>
                <input type="number" id="child_age" name="child_age"
                       value="<?= e($childAge) ?>" min="1" max="17" required>
            </div>

            <div class="form-group mt-1">
                <label for="child_allergies">Allergies alimentaires <span class="optional">(optionnel)</span></label>
                <textarea id="child_allergies" name="child_allergies" rows="2"><?= e($childAllergies) ?></textarea>
            </div>

            <?php if ((int) $user['credits'] > 0): ?>
                <div class="form-group form-group--checkbox mt-2">
                    <input type="checkbox" id="use_credit" name="use_credit" value="1">
                    <label for="use_credit">
                        Utiliser un de mes crédits (<?= (int) $user['credits'] ?> crédit(s) disponible(s)) – réservation gratuite
                    </label>
                </div>
            <?php endif; ?>

            <div class="mt-3">
                <button type="submit" name="action" value="pay" class="btn btn--primary">
                    💳 Procéder au paiement (<?= e(formatPrice((int) $session['price_cents'])) ?>)
                </button>
                <button type="submit" name="action" value="basket" class="btn btn--warning" style="margin-left:.5rem">
                    🛒 Ajouter au panier
                </button>
                <a href="<?= APP_BASE_URL ?>/session.php?id=<?= $sessionId ?>" class="btn btn--secondary" style="margin-left:.5rem">Annuler</a>
            </div>
        </form>
    </div>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
