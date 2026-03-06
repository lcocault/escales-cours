<?php
// public/payment_cancel.php – user cancelled on the payment provider's hosted page
require_once __DIR__ . '/init.php';

Auth::start();
$isBasket = isset($_GET['basket']);

if ($isBasket && Auth::isLoggedIn()) {
    // Clean up pending bookings created for the basket checkout
    $bookingIds = $_SESSION['basket_checkout_booking_ids'] ?? [];
    unset($_SESSION['basket_checkout_booking_ids']);

    if (!empty($bookingIds)) {
        $bookingModel = new BookingModel();
        foreach ($bookingIds as $bid) {
            $booking = $bookingModel->findById((int) $bid);
            if ($booking && $booking['status'] === 'pending') {
                $bookingModel->deleteById((int) $bid);
            }
        }
    }

    flash('info', 'Le paiement a été annulé. Les séances de votre panier n\'ont pas été réservées.');
    header('Location: ' . APP_BASE_URL . '/basket.php');
    exit;
}

flash('info', 'Le paiement a été annulé. Votre réservation n\'a pas été confirmée.');
header('Location: ' . APP_BASE_URL . '/my-sessions.php');
exit;

