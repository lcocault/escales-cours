# Escales Culinaires

Site web pour l'organisation d'ateliers de cuisine pour les enfants.

## Fonctionnalités

- 🍳 **Séances publiques** – liste des séances à venir avec résumé
- 🛒 **Réservation en ligne** – paiement via Stripe ou Square (configurable)
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

## Paiement

Le fournisseur de paiement est configurable via la constante `PAYMENT_PROVIDER` dans `config/config.php` :

- `'stripe'` – Stripe Checkout (par défaut)
- `'square'` – Square Payment Links

Voir `config/config.example.php` pour les clés requises selon le fournisseur choisi.

## CI / Déploiement automatique (GitHub Actions)

Le workflow `.github/workflows/deploy.yml` s'exécute automatiquement à chaque push :

1. **Tests** – installe les dépendances PHP et lance la suite PHPUnit.
2. **Déploiement** (sur push vers `main` uniquement, si les tests passent) – synchronise les fichiers vers AlwaysData via `rsync` over SSH.

### Secrets GitHub requis

Renseignez ces quatre secrets dans **Settings › Secrets and variables › Actions** de votre dépôt :

| Secret | Exemple | Description |
|---|---|---|
| `ALWAYSDATA_SSH_HOST` | `ssh-username.alwaysdata.net` | Hôte SSH AlwaysData |
| `ALWAYSDATA_SSH_USER` | `username` | Identifiant SSH AlwaysData |
| `ALWAYSDATA_SSH_KEY` | *(contenu du fichier `.pem`)* | Clé SSH privée (format PEM) |
| `ALWAYSDATA_REMOTE_PATH` | `/home/username/www` | Chemin absolu sur le serveur |
| `ALWAYSDATA_SSH_KNOWN_HOSTS` | *(sortie de `ssh-keyscan <host>`)* | Empreinte de l'hôte SSH vérifiée |

Pour obtenir la valeur de `ALWAYSDATA_SSH_KNOWN_HOSTS`, exécutez **une seule fois** depuis votre poste :

```bash
ssh-keyscan ssh-username.alwaysdata.net
```

Copiez la ligne affichée et enregistrez-la comme secret.

> **Remarque** : `config/config.php` n'est jamais déployé par le workflow. Copiez-le manuellement sur le serveur lors de la première installation.

## Développement

Voir [`.github/copilot-instructions.md`](.github/copilot-instructions.md) pour les guidelines complètes destinées aux assistants IA et aux contributeurs.
