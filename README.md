# Escales Culinaires

Site web pour l'organisation d'ateliers de cuisine pour les enfants.

## Fonctionnalités

- 🍳 **Séances publiques** – liste des séances à venir avec résumé
- 🛒 **Réservation en ligne** – paiement via Stripe
- 📚 **Contenu post-séance** – accessible uniquement aux participants confirmés
- 📸 **Photos privées** – soumises au consentement renseigné à l'inscription
- ⚙️ **Interface admin** – gestion des séances, participants, présences, crédits

## Pile technique

- **PHP 8.2+** · **PostgreSQL** · hébergement **AlwaysData**
- Thème graphique inspiré de *Ratatouille*

## Installation

```bash
# 1. Copier la configuration et renseigner vos valeurs
cp config/config.example.php config/config.php

# 2. Créer la base de données PostgreSQL et initialiser le schéma
psql -U <user> -d <dbname> -f database/schema.sql

# 3. Configurer le document root sur /public
#    (sur AlwaysData : Sites > votre site > Répertoire racine = /public)
```

## Développement

Voir [`.github/copilot-instructions.md`](.github/copilot-instructions.md) pour les guidelines complètes destinées aux assistants IA et aux contributeurs.

