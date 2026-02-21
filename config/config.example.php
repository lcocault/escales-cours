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

// SMTP
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_smtp_user');
define('SMTP_PASS', 'your_smtp_password');
define('SMTP_FROM', 'noreply@escales-cours.fr');
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

// Application
define('APP_BASE_URL', 'http://localhost');
define('APP_ENV', 'development'); // 'production' | 'development'
