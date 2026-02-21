<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * Tests for PaymentService.
 *
 * Each test runs in its own process because PHP constants cannot be redefined
 * once set, and PaymentService reads them via defined().
 */
#[RunTestsInSeparateProcesses]
class PaymentServiceTest extends TestCase
{
    protected function setUp(): void
    {
        // Minimal constants required by PaymentService (normally from config/config.php).
        if (!defined('APP_BASE_URL')) {
            define('APP_BASE_URL', 'http://localhost');
        }

        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../src/PaymentService.php';
    }

    // -------------------------------------------------------------------------
    // Stripe – demo fall-through (key is the placeholder value)
    // -------------------------------------------------------------------------

    public function testStripeDemoFallbackWhenKeyIsPlaceholder(): void
    {
        define('PAYMENT_PROVIDER', 'stripe');
        define('STRIPE_SECRET_KEY', 'sk_test_...');

        $url = PaymentService::createCheckoutUrl(42, 'Cours de Pain', 6500, 'eur');

        $this->assertStringContainsString('/payment_success.php', $url);
        $this->assertStringContainsString('booking_id=42', $url);
        $this->assertStringContainsString('_demo=1', $url);
    }

    // -------------------------------------------------------------------------
    // Square – demo fall-through (token is the placeholder value)
    // -------------------------------------------------------------------------

    public function testSquareDemoFallbackWhenTokenIsPlaceholder(): void
    {
        define('PAYMENT_PROVIDER', 'square');
        define('SQUARE_ACCESS_TOKEN', 'EAAAl...');
        define('SQUARE_LOCATION_ID', 'test_loc');
        define('SQUARE_ENVIRONMENT', 'sandbox');

        $url = PaymentService::createCheckoutUrl(7, 'Cours de Pâtisserie', 7500, 'eur');

        $this->assertStringContainsString('/payment_success.php', $url);
        $this->assertStringContainsString('booking_id=7', $url);
        $this->assertStringContainsString('_demo=1', $url);
    }

    // -------------------------------------------------------------------------
    // Unsupported provider
    // -------------------------------------------------------------------------

    public function testUnsupportedProviderThrowsRuntimeException(): void
    {
        define('PAYMENT_PROVIDER', 'paypal');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Unsupported PAYMENT_PROVIDER/');

        PaymentService::createCheckoutUrl(1, 'Cours', 5000, 'eur');
    }

    // -------------------------------------------------------------------------
    // Default provider (PAYMENT_PROVIDER not defined → 'stripe')
    // -------------------------------------------------------------------------

    public function testDefaultProviderFallsBackToStripeDemo(): void
    {
        // PAYMENT_PROVIDER intentionally not defined.
        define('STRIPE_SECRET_KEY', 'sk_test_...');

        $url = PaymentService::createCheckoutUrl(99, 'Cours', 8000, 'eur');

        $this->assertStringContainsString('_demo=1', $url);
        $this->assertStringContainsString('booking_id=99', $url);
    }
}
