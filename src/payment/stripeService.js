const Stripe = require('stripe');
const config = require('../config');

let stripeClient = null;

function getClient() {
  if (!stripeClient) {
    if (!config.stripe.secretKey) {
      throw new Error('STRIPE_SECRET_KEY environment variable is required when using Stripe.');
    }
    stripeClient = new Stripe(config.stripe.secretKey);
  }
  return stripeClient;
}

/**
 * Creates a Stripe Checkout Session for the given course.
 * @param {Object} params
 * @param {string} params.courseName
 * @param {number} params.amountCents  Amount in cents (e.g. 5000 = €50.00)
 * @param {string} params.currency     ISO currency code (e.g. "eur")
 * @param {string} params.successUrl
 * @param {string} params.cancelUrl
 * @returns {Promise<{checkoutUrl: string, sessionId: string}>}
 */
async function createCheckoutSession({ courseName, amountCents, currency, successUrl, cancelUrl }) {
  const stripe = getClient();
  const session = await stripe.checkout.sessions.create({
    payment_method_types: ['card'],
    line_items: [
      {
        price_data: {
          currency,
          product_data: { name: courseName },
          unit_amount: amountCents,
        },
        quantity: 1,
      },
    ],
    mode: 'payment',
    success_url: successUrl,
    cancel_url: cancelUrl,
  });
  return { checkoutUrl: session.url, sessionId: session.id };
}

module.exports = { createCheckoutSession };
