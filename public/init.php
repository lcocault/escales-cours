<?php
// public/init.php – bootstraps the application; included at top of every public page

define('ROOT_DIR', dirname(__DIR__));

require_once ROOT_DIR . '/config/config.php';
require_once ROOT_DIR . '/vendor/autoload.php';
require_once ROOT_DIR . '/src/Database.php';
require_once ROOT_DIR . '/src/Auth.php';
require_once ROOT_DIR . '/src/UserModel.php';
require_once ROOT_DIR . '/src/SessionModel.php';
require_once ROOT_DIR . '/src/BookingModel.php';
require_once ROOT_DIR . '/src/BasketModel.php';
require_once ROOT_DIR . '/src/GeneralMessageModel.php';
require_once ROOT_DIR . '/src/Mailer.php';
require_once ROOT_DIR . '/src/PaymentService.php';

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

// Helper: check whether a session row is in the past (after end_time)
function sessionIsPast(array $session): bool
{
    return strtotime($session['session_date'] . ' ' . $session['end_time']) < time();
}

// Helper: returns the number of items in the current user's basket.
// Uses a static variable so the DB is queried at most once per request.
function currentBasketCount(): int
{
    static $count = null;
    if ($count === null && Auth::isLoggedIn()) {
        $count = (new BasketModel())->countByUser(Auth::currentUserId());
    }
    return $count ?? 0;
}
function ageCategoryLabel(string $category): string
{
    $labels = [
        '3-5'  => '3 à 5 ans',
        '6-12' => '6 à 12 ans',
        '13+'  => '13 ans et +',
    ];
    return $labels[$category] ?? $category;
}
