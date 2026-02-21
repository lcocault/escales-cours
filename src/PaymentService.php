<?php
// src/PaymentService.php – provider-agnostic payment service.
// Delegates to Stripe or Square depending on the PAYMENT_PROVIDER constant.

use Square\SquareClient;
use Square\Environments as SquareEnvironments;
use Square\Checkout\PaymentLinks\Requests\CreatePaymentLinkRequest;
use Square\Types\QuickPay;
use Square\Types\Money;

class PaymentService
{
    /**
     * Creates a checkout URL for the given booking and redirects the user to it.
     *
     * @param int    $bookingId     The booking record ID (used as reference and in return URLs).
     * @param string $itemName      Human-readable name of the item being paid for.
     * @param int    $amountCents   Price in the smallest currency unit (e.g. euro cents).
     * @param string $currency      ISO 4217 currency code (e.g. "eur", "usd").
     * @return string               The redirect URL of the hosted payment page.
     * @throws RuntimeException     If the configured provider is unsupported or misconfigured.
     */
    public static function createCheckoutUrl(
        int $bookingId,
        string $itemName,
        int $amountCents,
        string $currency
    ): string {
        $provider = defined('PAYMENT_PROVIDER') ? strtolower(PAYMENT_PROVIDER) : 'stripe';

        return match ($provider) {
            'stripe' => self::stripeCheckoutUrl($bookingId, $itemName, $amountCents, $currency),
            'square' => self::squareCheckoutUrl($bookingId, $itemName, $amountCents, $currency),
            default  => throw new RuntimeException(
                "Unsupported PAYMENT_PROVIDER \"$provider\". Must be \"stripe\" or \"square\"."
            ),
        };
    }

    // -------------------------------------------------------------------------
    // Stripe
    // -------------------------------------------------------------------------

    private static function stripeCheckoutUrl(
        int $bookingId,
        string $itemName,
        int $amountCents,
        string $currency
    ): string {
        if (!defined('STRIPE_SECRET_KEY') || STRIPE_SECRET_KEY === '' || STRIPE_SECRET_KEY === 'sk_test_...') {
            // Stripe is not configured – fall back to demo mode.
            return APP_BASE_URL . '/payment_success.php?booking_id=' . $bookingId . '&_demo=1';
        }

        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => [[
                'price_data' => [
                    'currency'     => strtolower($currency),
                    'product_data' => ['name' => $itemName],
                    'unit_amount'  => $amountCents,
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => APP_BASE_URL . '/payment_success.php?booking_id=' . $bookingId
                             . '&payment_intent={CHECKOUT_SESSION_ID}',
            'cancel_url'  => APP_BASE_URL . '/payment_cancel.php?booking_id=' . $bookingId,
            'metadata'    => ['booking_id' => $bookingId],
        ]);

        return $session->url;
    }

    // -------------------------------------------------------------------------
    // Square
    // -------------------------------------------------------------------------

    private static function squareCheckoutUrl(
        int $bookingId,
        string $itemName,
        int $amountCents,
        string $currency
    ): string {
        if (!defined('SQUARE_ACCESS_TOKEN') || SQUARE_ACCESS_TOKEN === '' || SQUARE_ACCESS_TOKEN === 'EAAAl...') {
            // Square is not configured – fall back to demo mode.
            return APP_BASE_URL . '/payment_success.php?booking_id=' . $bookingId . '&_demo=1';
        }

        $environment = (defined('SQUARE_ENVIRONMENT') && SQUARE_ENVIRONMENT === 'production')
            ? SquareEnvironments::Production->value
            : SquareEnvironments::Sandbox->value;

        $client = new SquareClient(
            token: SQUARE_ACCESS_TOKEN,
            options: ['baseUrl' => $environment],
        );

        $request = new CreatePaymentLinkRequest([
            'idempotencyKey' => uniqid('booking_' . $bookingId . '_', true),
            'description'    => 'Booking #' . $bookingId,
            'quickPay'       => new QuickPay([
                'name'       => $itemName,
                'locationId' => SQUARE_LOCATION_ID,
                'priceMoney' => new Money([
                    'amount'   => $amountCents,
                    'currency' => strtoupper($currency),
                ]),
            ]),
            'checkoutOptions' => new \Square\Types\CheckoutOptions([
                'redirectUrl' => APP_BASE_URL . '/payment_success.php?booking_id=' . $bookingId,
            ]),
        ]);

        $response = $client->checkout->paymentLinks->create($request);
        $link = $response->getPaymentLink();

        if ($link === null || $link->getUrl() === null) {
            throw new RuntimeException('Square did not return a payment link URL.');
        }

        return $link->getUrl();
    }
}
