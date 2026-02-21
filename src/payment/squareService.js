const { SquareClient, SquareEnvironment } = require('square');
const config = require('../config');

let squareClient = null;

function getClient() {
  if (!squareClient) {
    if (!config.square.accessToken) {
      throw new Error('SQUARE_ACCESS_TOKEN environment variable is required when using Square.');
    }
    squareClient = new SquareClient({
      token: config.square.accessToken,
      environment:
        config.square.environment === 'production'
          ? SquareEnvironment.Production
          : SquareEnvironment.Sandbox,
    });
  }
  return squareClient;
}

/**
 * Creates a Square Payment Link for the given course.
 * @param {Object} params
 * @param {string} params.courseName
 * @param {number} params.amountCents  Amount in smallest currency unit (e.g. 5000 = €50.00)
 * @param {string} params.currency     ISO currency code (e.g. "EUR")
 * @param {string} params.successUrl
 * @returns {Promise<{checkoutUrl: string, orderId: string}>}
 */
async function createCheckoutSession({ courseName, amountCents, currency, successUrl }) {
  const client = getClient();

  if (!config.square.locationId) {
    throw new Error('SQUARE_LOCATION_ID environment variable is required when using Square.');
  }

  const idempotencyKey = `${Date.now()}-${Math.random().toString(36).slice(2)}`;

  const response = await client.checkout.paymentLinks.create({
    idempotencyKey,
    order: {
      locationId: config.square.locationId,
      lineItems: [
        {
          name: courseName,
          quantity: '1',
          basePriceMoney: {
            amount: BigInt(amountCents),
            currency: currency.toUpperCase(),
          },
        },
      ],
    },
    checkoutOptions: {
      redirectUrl: successUrl,
    },
  });

  const link = response.paymentLink;
  return { checkoutUrl: link.url, orderId: link.orderId };
}

module.exports = { createCheckoutSession };
