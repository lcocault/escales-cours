# Copilot Instructions – Escales Culinaires

## Project Overview

This is a PHP website for organising cooking sessions for children.
It is hosted on **AlwaysData** with a **PostgreSQL** database.

### Key Features
- Public session listing (upcoming sessions with summary)
- User registration and authentication
- Session booking with online payment
- Email notifications (attendee + admin) on booking
- Post-session detailed content (visible only to confirmed attendees)
- Private content (photos) gated on photo-consent flag set at registration
- Admin area: manage sessions, view attendees, confirm attendance, issue free credits

### Theme
Childish and cheerful, inspired by the *Ratatouille* animated film.
Use warm reds, sunny yellows, and kitchen greens with rounded, friendly typography.

---

## Technology Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.2+ |
| Database | PostgreSQL (PDO driver) |
| Hosting | AlwaysData shared hosting |
| Email | PHP `mail()` or SMTP via PHPMailer |
| Payment | Stripe or Square – configured via `PAYMENT_PROVIDER` constant (`'stripe'` \| `'square'`) |
| CSS | Plain CSS (no framework) – mobile-first |
| Templating | Native PHP templates in `/templates/` |
| Testing | PHPUnit 11+ |

---

## Project Structure

```
/
├── .github/
│   └── copilot-instructions.md   ← this file
├── config/
│   ├── config.php                ← environment-specific constants (never commit secrets)
│   └── config.example.php        ← template showing which constants are needed
├── database/
│   ├── schema.sql                ← full DDL; run once to provision a fresh DB
│   └── migrations/               ← incremental changes; one file per schema change
│       └── YYYY-MM-DD_NNN_description.sql
├── public/                       ← web root (document root on AlwaysData)
│   ├── index.php                 ← homepage: upcoming sessions
│   ├── session.php               ← session detail (public summary)
│   ├── book.php                  ← booking form + Stripe redirect
│   ├── payment_success.php       ← Stripe success callback
│   ├── payment_cancel.php        ← Stripe cancel callback
│   ├── login.php
│   ├── register.php
│   ├── profile.php
│   ├── logout.php
│   ├── my-sessions.php           ← attendee's booked sessions
│   ├── session-content.php       ← post-session detailed content (attendees only)
│   ├── admin/
│   │   ├── index.php             ← admin dashboard
│   │   ├── sessions.php          ← list / create / edit sessions
│   │   ├── session-edit.php      ← session form
│   │   ├── session-delete.php    ← delete action
│   │   ├── attendees.php         ← attendees list per session
│   │   └── confirm-attendance.php← confirm / credit actions
│   ├── css/
│   │   └── style.css
│   └── img/                      ← static assets
├── src/
│   ├── Database.php              ← PDO singleton
│   ├── Auth.php                  ← login / register / session helpers
│   ├── SessionModel.php          ← cooking-session CRUD
│   ├── BookingModel.php          ← booking CRUD
│   ├── UserModel.php             ← user CRUD
│   ├── Mailer.php                ← email helpers
│   └── PaymentService.php        ← Stripe / Square abstraction (reads PAYMENT_PROVIDER)
└── templates/
    ├── header.php
    ├── footer.php
    └── flash.php                 ← flash message component
```

---

## Coding Standards

- **PHP**: Follow PSR-12.  All files start with `<?php` (no closing tag).
- **SQL**: Use prepared statements with named parameters (`:param`) via PDO.  Never interpolate user input.
- **HTML**: HTML5, semantic elements, UTF-8.
- **CSS**: BEM-like class names, custom properties for the colour palette.
- **Security**:
  - Passwords hashed with `password_hash($password, PASSWORD_BCRYPT)`.
  - CSRF tokens on every state-changing form.
  - Output escaped with `htmlspecialchars()` (alias `e()`).
  - Session IDs regenerated after login (`session_regenerate_id(true)`).
  - Role check (`isAdmin()`) at the top of every admin page.
  - Uploaded files stored outside the web root or with strict type validation.
- **Error handling**: Use exceptions; catch at the controller level and show a friendly error page. Never expose stack traces in production.

---

## Database Conventions

- Snake_case table and column names.
- Every table has a `SERIAL PRIMARY KEY` column named `id`.
- Timestamps use `TIMESTAMPTZ` defaulting to `NOW()`.
- Soft-delete via `deleted_at TIMESTAMPTZ NULL`.
- Foreign keys are enforced with `ON DELETE RESTRICT` unless noted otherwise.

---

## Database Migrations

`database/schema.sql` is the **full baseline DDL**, used only to provision a brand-new database from scratch.

**Every subsequent schema change** (new column, new table, index, constraint tweak…) must be written as a separate migration file in `database/migrations/`.

### File naming

```
YYYY-MM-DD_NNN_short_description.sql
```

Examples:
```
2026-02-22_001_add_phone_to_users.sql
2026-02-22_002_create_waitlist_table.sql
```

- `YYYY-MM-DD` – date the migration was authored.
- `NNN` – zero-padded sequence number, incrementing within the project lifetime.
- `short_description` – snake_case summary of what changes.

### Rules

- Each migration file must be **idempotent** where practical (use `IF NOT EXISTS`, `IF EXISTS`, `DO $$ … $$` guards).
- Never modify an already-committed migration file; add a new one instead.
- Always update `database/schema.sql` in the same commit so it stays an accurate picture of the current full schema.
- Begin every migration with a short comment stating its purpose:
  ```sql
  -- Migration 2026-02-22_001: add phone column to users
  ```

### Applying migrations on the server

Migrations are **not run automatically** by the CI/CD workflow. After deploying, apply them manually via SSH:

```bash
psql -h <DB_HOST> -U <DB_USER> -d <DB_NAME> \
  -f ~/www/database/migrations/YYYY-MM-DD_NNN_description.sql
```

Or paste the file contents into phpPgAdmin's SQL editor.

---

## Environment / Configuration

All secrets and environment-specific values go in `config/config.php` (git-ignored).
Copy `config/config.example.php` to `config/config.php` and fill in the values.

Required constants:

```php
DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS
SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_FROM, SMTP_FROM_NAME
ADMIN_EMAIL
PAYMENT_PROVIDER          // 'stripe' | 'square'  (defaults to 'stripe')
// If PAYMENT_PROVIDER = 'stripe':
STRIPE_PUBLIC_KEY, STRIPE_SECRET_KEY, STRIPE_WEBHOOK_SECRET
// If PAYMENT_PROVIDER = 'square':
SQUARE_ACCESS_TOKEN, SQUARE_LOCATION_ID, SQUARE_ENVIRONMENT  // 'sandbox' | 'production'
APP_BASE_URL          // e.g. https://escales-cours.alwaysdata.net
APP_ENV               // 'production' | 'development'
```

---

## Key Business Rules

1. **Booking**: A booking is only confirmed after successful payment.
2. **Seat count**: `sessions.remaining_seats` is decremented on confirmed booking and incremented on cancellation.
3. **Attendance confirmation**: Only the admin can mark a booking as `attended`.  This unlocks detailed content for that user + session.
4. **Free credit**: Admin can mark a booking as `credited`; this creates a `credits` record the user can apply to a future booking.
5. **Photo consent**: Stored as `users.photo_consent BOOLEAN`.  Private photo content is only shown when `photo_consent = TRUE`.
6. **Roles**: `users.role` is either `'user'` or `'admin'`.

---

## Format des séances

Pour garantir une présentation homogène des futures séances, chaque fiche de session doit contenir exactement et uniquement les quatre sections suivantes, rédigées en français clair et concis :

- Résumé (quelques lignes) : une accroche courte qui décrit l'objectif global de la séance et l'activité pratique finale (1–3 phrases).
- Objectifs pédagogiques : liste courte (3–6 puces) des compétences ou connaissances visées par la séance.
- Contenu théorique : points clés présentés pendant la séance (explications courtes, vocabulaire et notions utiles). Ne pas rappeler des informations générales sur la série de cours ou l'âge du public — rester centré sur le thème de la séance.
- Recette pratique : liste d'ingrédients (quantités) et étapes de préparation clairement numérotées. Indiquer les allergènes principaux en tête et proposer alternatives éventuelles.

Règles de style
- Rédiger en phrases courtes et en langage accessible.
- Éviter les répétitions entre sections : la théorie et la recette sont complémentaires, pas redondantes.
- Indiquer les allergènes et substitutions possibles directement dans la section "Recette pratique".
- Fournir des quantités adaptées à la taille d'un atelier (ex. portions pour 6 enfants) quand pertinent.

Champs admin recommandés
- `title` : Titre court
- `short_description` : Résumé (quelques lignes)
- `objectives` : Objectifs pédagogiques (liste)
- `theory` : Contenu théorique (texte court)
- `recipe` : Recette pratique (ingrédients + étapes + allergènes)

Exemple minimal à coller dans l'admin :

Title: Les produits laitiers — pâte à crêpes
Short_description: Atelier pratique autour des produits laitiers et préparation d'une pâte à crêpes.
Objectives: Comprendre l'origine du lait; mesurer des ingrédients; techniques de mélange et de cuisson.
Theory: Origine du lait; rôle du lait et du beurre dans la pâte; repos de la pâte.
Recipe: Ingrédients: 250g farine, 3 œufs, 600mL lait, 50g beurre; Étapes: 1) Mélanger..., 2) Repos 20min, 3) Cuisson...; Allergènes: lait, œuf.

---

## Testing

- Tests live in `tests/` and use PHPUnit.
- Run: `./vendor/bin/phpunit`
- Unit tests mock the DB; integration tests use a dedicated test PostgreSQL schema.

---

## Deployment (AlwaysData)

1. Upload files via SFTP or Git deploy hook (CI/CD via GitHub Actions).
2. Set the document root to `/public`.
3. **First install only**: create a PostgreSQL database and run `database/schema.sql`.
4. **Each subsequent deploy that includes schema changes**: apply the new migration file(s) from `database/migrations/` manually (see *Database Migrations* above).
5. Copy `config/config.example.php` → `config/config.php` on the server and fill in credentials (done once; never overwritten by rsync).
6. Ensure `session.save_path` is writable.
