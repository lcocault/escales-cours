const express = require('express');
const paymentService = require('../payment/paymentService');
const config = require('../config');

const router = express.Router();

const COURSES = [
  { id: 'cours-pain', name: 'Cours de Pain Artisanal', amountCents: 6500, currency: 'eur' },
  { id: 'cours-patisserie', name: 'Cours de Pâtisserie', amountCents: 7500, currency: 'eur' },
  { id: 'cours-cuisine-francaise', name: 'Cuisine Française Traditionnelle', amountCents: 8000, currency: 'eur' },
];

router.get('/checkout/:courseId', async (req, res) => {
  const course = COURSES.find((c) => c.id === req.params.courseId);
  if (!course) {
    return res.status(404).json({ error: 'Course not found' });
  }

  const baseUrl = `${req.protocol}://${req.get('host')}`;
  try {
    const result = await paymentService.createCheckoutSession({
      courseName: course.name,
      amountCents: course.amountCents,
      currency: course.currency,
      successUrl: `${baseUrl}/success?course=${encodeURIComponent(course.name)}`,
      cancelUrl: `${baseUrl}/`,
    });
    res.redirect(result.checkoutUrl);
  } catch (err) {
    console.error('Payment error:', err);
    res.status(500).json({ error: 'Payment provider error', details: err.message });
  }
});

router.get('/courses', (req, res) => {
  res.json({ provider: config.paymentProvider, courses: COURSES });
});

module.exports = router;
