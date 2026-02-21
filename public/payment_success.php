<?php
// public/payment_success.php – Stripe payment success callback
// In production, verify the Stripe webhook instead of relying on this redirect.
require_once __DIR__ . '/init.php';
Auth::requireLogin();

$bookingId = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;
$isDemo    = isset($_GET['_demo']); // true in dev when Stripe is not configured

$bookingModel = new BookingModel();
$booking = $bookingModel->findById($bookingId);

if (!$booking || (int) $booking['user_id'] !== Auth::currentUserId()) {
    flash('error', 'Réservation introuvable.');
    header('Location: ' . APP_BASE_URL . '/');
    exit;
}

if ($booking['status'] === 'pending') {
    // In production: validate via Stripe webhook; here we confirm immediately for demo
    $paymentIntentId = $isDemo ? 'demo_' . $bookingId : ($_GET['payment_intent'] ?? '');
    $bookingModel->confirm($bookingId, $paymentIntentId);

    $sessionModel = new SessionModel();
    $session = $sessionModel->findById((int) $booking['session_id']);
    $sessionModel->decrementSeats((int) $booking['session_id']);

    $userModel = new UserModel();
    $user = $userModel->findById(Auth::currentUserId());

    Mailer::sendBookingConfirmationToAttendee($user, $session);
    Mailer::sendBookingNotificationToAdmin($user, $session);
}

flash('success', 'Votre réservation est confirmée ! Vous recevrez un e-mail de confirmation.');
header('Location: ' . APP_BASE_URL . '/my-sessions.php');
exit;
