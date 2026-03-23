<?php
// ============================================================
// config/config.example.php
// Copy this file to config/config.php and fill in your values.
// NEVER commit config/config.php to version control.
// ============================================================

// Database
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'escales_cours');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');

// SMTP – used by PHPMailer to send transactional emails
// Gmail example (recommended):
//   1. Enable 2-Step Verification on your Google account.
//   2. Generate an App Password at https://myaccount.google.com/apppasswords
//      (select "Mail" and your device, then copy the 16-character password).
//   3. Fill in the values below.
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-address@gmail.com');
define('SMTP_PASS', 'your-gmail-app-password');
define('SMTP_FROM', 'your-address@gmail.com');
define('SMTP_FROM_NAME', 'Escales Culinaires');

// Admin contact
define('ADMIN_EMAIL', 'admin@escales-cours.fr');

// Stripe (required when PAYMENT_PROVIDER = 'stripe')
define('STRIPE_PUBLIC_KEY', 'pk_test_...');
define('STRIPE_SECRET_KEY', 'sk_test_...');
define('STRIPE_WEBHOOK_SECRET', 'whsec_...');

// Square (required when PAYMENT_PROVIDER = 'square')
define('SQUARE_ACCESS_TOKEN', 'EAAAl...');
define('SQUARE_LOCATION_ID', 'your_location_id');
define('SQUARE_ENVIRONMENT', 'sandbox'); // 'sandbox' | 'production'

// Payment provider: 'stripe' | 'square'  (defaults to 'stripe' if omitted)
define('PAYMENT_PROVIDER', 'square');

// Session confirmation
define('SESSION_MIN_ATTENDEES', 2); // minimum confirmed bookings required to hold a session

// Application
define('APP_BASE_URL', 'http://localhost');
define('APP_ENV', 'development'); // 'production' | 'development'
