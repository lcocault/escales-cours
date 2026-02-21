<?php
// public/admin/confirm-attendance.php – mark attended / absent / credited
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE_URL . '/admin/sessions.php');
    exit;
}

Auth::verifyCsrf();

$bookingId = isset($_POST['booking_id']) ? (int) $_POST['booking_id'] : 0;
$action    = $_POST['action']    ?? '';
$sessionId = isset($_POST['session_id']) ? (int) $_POST['session_id'] : 0;

$bookingModel = new BookingModel();
$booking = $bookingModel->findById($bookingId);

if (!$booking) {
    flash('error', 'Réservation introuvable.');
    header('Location: ' . APP_BASE_URL . '/admin/sessions.php');
    exit;
}

switch ($action) {
    case 'attended':
        $bookingModel->markAttended($bookingId);
        flash('success', 'Participant marqué comme présent.');
        break;

    case 'absent':
        $bookingModel->markAbsent($bookingId);
        flash('info', 'Participant marqué comme absent.');
        break;

    case 'credited':
        $bookingModel->markCredited($bookingId);
        // Add a credit to the user
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO credits (user_id, booking_id, reason) VALUES (:uid, :bid, :reason)'
        );
        $stmt->execute([
            ':uid'    => $booking['user_id'],
            ':bid'    => $bookingId,
            ':reason' => 'Absence justifiée',
        ]);
        $userModel = new UserModel();
        $userModel->updateCredits((int) $booking['user_id'], 1);
        flash('success', 'Crédit accordé au participant.');
        break;

    default:
        flash('error', 'Action inconnue.');
}

header('Location: ' . APP_BASE_URL . '/admin/attendees.php?session_id=' . $sessionId);
exit;
