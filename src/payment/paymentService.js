const config = require('../config');

/**
 * Unified payment service.
 * Delegates to either Stripe or Square based on PAYMENT_PROVIDER configuration.
 */
function getProvider() {
  if (config.paymentProvider === 'stripe') {
    return require('./stripeService');
  }
  return require('./squareService');
}

/**
 * Creates a checkout session/link using the configured payment provider.
 * @param {Object} params
 * @param {string} params.courseName
 * @param {number} params.amountCents
 * @param {string} params.currency
 * @param {string} params.successUrl
 * @param {string} params.cancelUrl
 * @returns {Promise<{checkoutUrl: string}>}
 */
async function createCheckoutSession(params) {
  return getProvider().createCheckoutSession(params);
}

module.exports = { createCheckoutSession, getProvider };
