require('dotenv').config();
const express = require('express');
const path = require('path');
const config = require('./config');
const paymentRouter = require('./routes/payment');

const app = express();

app.use(express.json());
app.use(express.urlencoded({ extended: false }));
app.use(express.static(path.join(__dirname, '..', 'public')));

app.use('/api', paymentRouter);

app.get('/success', (req, res) => {
  const course = req.query.course || 'votre cours';
  res.send(`
    <!DOCTYPE html>
    <html lang="fr">
    <head><meta charset="UTF-8"><title>Paiement confirmé – Les Escales Culinaires</title>
    <style>body{font-family:sans-serif;text-align:center;margin-top:80px;color:#333}</style>
    </head>
    <body>
      <h1>✅ Paiement confirmé !</h1>
      <p>Merci pour votre inscription à <strong>${course}</strong>.</p>
      <p>Vous recevrez un email de confirmation sous peu.</p>
      <a href="/">← Retour à l'accueil</a>
    </body>
    </html>
  `);
});

if (require.main === module) {
  app.listen(config.port, () => {
    console.log(`Les Escales Culinaires running on port ${config.port}`);
    console.log(`Payment provider: ${config.paymentProvider}`);
  });
}

module.exports = app;
