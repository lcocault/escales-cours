<?php
// public/init.php – bootstraps the application; included at top of every public page

define('ROOT_DIR', dirname(__DIR__));

require_once ROOT_DIR . '/config/config.php';
require_once ROOT_DIR . '/src/Database.php';
require_once ROOT_DIR . '/src/Auth.php';
require_once ROOT_DIR . '/src/UserModel.php';
require_once ROOT_DIR . '/src/SessionModel.php';
require_once ROOT_DIR . '/src/BookingModel.php';
require_once ROOT_DIR . '/src/Mailer.php';

// Helper: escape output (accepts null, returns empty string for null)
function e(?string $value): string
{
    if ($value === null) {
        return '';
    }
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Helper: set a flash message
function flash(string $type, string $message): void
{
    Auth::start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Helper: format a price in cents to a human-readable euro string
function formatPrice(int $cents): string
{
    return number_format($cents / 100, 2, ',', ' ') . ' €';
}

// Helper: format a date string (Y-m-d) to French long format
function formatDate(string $date): string
{
    $ts = strtotime($date);
    $days   = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $months = [
        1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
        'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
    ];
    return $days[(int) date('w', $ts)] . ' '
         . (int) date('j', $ts) . ' '
         . $months[(int) date('n', $ts)] . ' '
         . date('Y', $ts);
}
