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
| Payment | Stripe (Checkout / Payment Intents) |
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
│   └── schema.sql                ← full DDL; run once to provision the DB
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
│   └── Mailer.php                ← email helpers
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

## Environment / Configuration

All secrets and environment-specific values go in `config/config.php` (git-ignored).
Copy `config/config.example.php` to `config/config.php` and fill in the values.

Required constants:

```php
DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS
SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_FROM, SMTP_FROM_NAME
ADMIN_EMAIL
STRIPE_PUBLIC_KEY, STRIPE_SECRET_KEY, STRIPE_WEBHOOK_SECRET
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

## Testing

- Tests live in `tests/` and use PHPUnit.
- Run: `./vendor/bin/phpunit`
- Unit tests mock the DB; integration tests use a dedicated test PostgreSQL schema.

---

## Deployment (AlwaysData)

1. Upload files via SFTP or Git deploy hook.
2. Set the document root to `/public`.
3. Create a PostgreSQL database and run `database/schema.sql`.
4. Copy `config/config.example.php` → `config/config.php` and fill in credentials.
5. Ensure `session.save_path` is writable.
