const request = require('supertest');

describe('GET /api/courses', () => {
  const originalEnv = process.env;

  beforeEach(() => {
    jest.resetModules();
    process.env = { ...originalEnv };
  });

  afterAll(() => {
    process.env = originalEnv;
  });

  test('returns courses list with payment provider when using square', async () => {
    process.env.PAYMENT_PROVIDER = 'square';
    const app = require('../src/app');
    const res = await request(app).get('/api/courses');
    expect(res.status).toBe(200);
    expect(res.body.provider).toBe('square');
    expect(Array.isArray(res.body.courses)).toBe(true);
    expect(res.body.courses.length).toBeGreaterThan(0);
  });

  test('returns courses list with payment provider when using stripe', async () => {
    process.env.PAYMENT_PROVIDER = 'stripe';
    const app = require('../src/app');
    const res = await request(app).get('/api/courses');
    expect(res.status).toBe(200);
    expect(res.body.provider).toBe('stripe');
  });

  test('returns 404 for unknown course checkout', async () => {
    process.env.PAYMENT_PROVIDER = 'square';
    const app = require('../src/app');
    const res = await request(app).get('/api/checkout/unknown-course');
    expect(res.status).toBe(404);
    expect(res.body.error).toBe('Course not found');
  });
});
