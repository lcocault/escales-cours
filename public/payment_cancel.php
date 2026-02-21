<?php
// public/payment_cancel.php – user cancelled on the payment provider's hosted page
require_once __DIR__ . '/init.php';

flash('info', 'Le paiement a été annulé. Votre réservation n\'a pas été confirmée.');
header('Location: ' . APP_BASE_URL . '/my-sessions.php');
exit;
