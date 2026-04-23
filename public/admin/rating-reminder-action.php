<?php
// public/admin/rating-reminder-action.php – send reminder or dismiss a booking from rating list
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE_URL . '/admin/rating-reminders.php');
    exit;
}

Auth::verifyCsrf();

$bookingId = isset($_POST['booking_id']) ? (int) $_POST['booking_id'] : 0;
$action    = $_POST['action'] ?? '';

$bookingModel = new BookingModel();
$booking = $bookingModel->findById($bookingId);

if (!$booking) {
    flash('error', 'Réservation introuvable.');
    header('Location: ' . APP_BASE_URL . '/admin/rating-reminders.php');
    exit;
}

switch ($action) {
    case 'remind':
        $sessionModel = new SessionModel();
        $session = $sessionModel->findById((int) $booking['session_id']);
        $userModel = new UserModel();
        $user = $userModel->findById((int) $booking['user_id']);
        if ($session && $user) {
            Mailer::sendRatingReminder($user, $session);
            flash('success', 'Rappel envoyé à ' . $user['first_name'] . ' ' . $user['last_name'] . '.');
        } else {
            flash('error', 'Impossible d\'envoyer le rappel : données introuvables.');
        }
        break;

    case 'dismiss':
        $bookingModel->dismissRatingReminder($bookingId);
        flash('info', 'Participation ignorée.');
        break;

    default:
        flash('error', 'Action inconnue.');
}

header('Location: ' . APP_BASE_URL . '/admin/rating-reminders.php');
exit;
