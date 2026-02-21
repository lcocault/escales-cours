describe('paymentService provider routing', () => {
  const originalEnv = process.env;

  beforeEach(() => {
    jest.resetModules();
    process.env = { ...originalEnv };
  });

  afterAll(() => {
    process.env = originalEnv;
  });

  test('getProvider returns squareService when provider is square', () => {
    process.env.PAYMENT_PROVIDER = 'square';
    const { getProvider } = require('../src/payment/paymentService');
    const provider = getProvider();
    expect(provider).toBe(require('../src/payment/squareService'));
  });

  test('getProvider returns stripeService when provider is stripe', () => {
    process.env.PAYMENT_PROVIDER = 'stripe';
    const { getProvider } = require('../src/payment/paymentService');
    const provider = getProvider();
    expect(provider).toBe(require('../src/payment/stripeService'));
  });

  test('both providers expose a createCheckoutSession function', () => {
    const stripe = require('../src/payment/stripeService');
    const square = require('../src/payment/squareService');
    expect(typeof stripe.createCheckoutSession).toBe('function');
    expect(typeof square.createCheckoutSession).toBe('function');
  });
});
