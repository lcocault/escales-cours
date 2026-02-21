require('dotenv').config();

/**
 * Payment provider configuration.
 * Set PAYMENT_PROVIDER environment variable to "stripe" or "square".
 * Defaults to "square".
 */
const SUPPORTED_PROVIDERS = ['stripe', 'square'];

const provider = (process.env.PAYMENT_PROVIDER || 'square').toLowerCase();

if (!SUPPORTED_PROVIDERS.includes(provider)) {
  throw new Error(
    `Unsupported PAYMENT_PROVIDER "${provider}". Must be one of: ${SUPPORTED_PROVIDERS.join(', ')}`
  );
}

const config = {
  paymentProvider: provider,

  stripe: {
    secretKey: process.env.STRIPE_SECRET_KEY,
    publishableKey: process.env.STRIPE_PUBLISHABLE_KEY,
  },

  square: {
    accessToken: process.env.SQUARE_ACCESS_TOKEN,
    locationId: process.env.SQUARE_LOCATION_ID,
    environment: process.env.SQUARE_ENVIRONMENT || 'sandbox',
  },

  port: parseInt(process.env.PORT, 10) || 3000,
};

module.exports = config;
