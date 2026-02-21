describe('Payment configuration', () => {
  const originalEnv = process.env;

  beforeEach(() => {
    jest.resetModules();
    process.env = { ...originalEnv };
  });

  afterAll(() => {
    process.env = originalEnv;
  });

  test('defaults to "square" when PAYMENT_PROVIDER is not set', () => {
    delete process.env.PAYMENT_PROVIDER;
    const config = require('../src/config');
    expect(config.paymentProvider).toBe('square');
  });

  test('uses "stripe" when PAYMENT_PROVIDER=stripe', () => {
    process.env.PAYMENT_PROVIDER = 'stripe';
    const config = require('../src/config');
    expect(config.paymentProvider).toBe('stripe');
  });

  test('uses "square" when PAYMENT_PROVIDER=square', () => {
    process.env.PAYMENT_PROVIDER = 'square';
    const config = require('../src/config');
    expect(config.paymentProvider).toBe('square');
  });

  test('normalises provider name to lowercase', () => {
    process.env.PAYMENT_PROVIDER = 'STRIPE';
    const config = require('../src/config');
    expect(config.paymentProvider).toBe('stripe');
  });

  test('throws for unsupported provider values', () => {
    process.env.PAYMENT_PROVIDER = 'paypal';
    expect(() => require('../src/config')).toThrow(/Unsupported PAYMENT_PROVIDER/);
  });
});
