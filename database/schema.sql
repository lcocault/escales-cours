-- ============================================================
-- database/schema.sql
-- Full DDL for the Escales Culinaires PostgreSQL database.
-- Run once to provision the schema.
-- ============================================================

-- Users -------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id              SERIAL PRIMARY KEY,
    email           VARCHAR(255) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    first_name      VARCHAR(100) NOT NULL,
    last_name       VARCHAR(100) NOT NULL,
    phone           VARCHAR(30),
    phone2          VARCHAR(30),
    role            VARCHAR(20)  NOT NULL DEFAULT 'user' CHECK (role IN ('user', 'admin')),
    photo_consent   BOOLEAN      NOT NULL DEFAULT FALSE,
    credits         INTEGER      NOT NULL DEFAULT 0,
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    deleted_at      TIMESTAMPTZ
);

-- Cooking sessions --------------------------------------------
CREATE TABLE IF NOT EXISTS sessions (
    id                  SERIAL PRIMARY KEY,
    title               VARCHAR(255)    NOT NULL,
    theme               VARCHAR(255)    NOT NULL,
    session_date        DATE            NOT NULL,
    start_time          TIME            NOT NULL,
    end_time            TIME            NOT NULL,
    max_attendees       INTEGER         NOT NULL CHECK (max_attendees > 0),
    remaining_seats     INTEGER         NOT NULL CHECK (remaining_seats >= 0),
    price_cents         INTEGER         NOT NULL CHECK (price_cents >= 0),  -- price in euro cents
    status              VARCHAR(20)     NOT NULL DEFAULT 'pending'
                            CHECK (status IN ('pending', 'confirmed', 'cancelled')),
    age_category        VARCHAR(10)     NOT NULL DEFAULT '6-12'
                            CHECK (age_category IN ('3-5', '6-12', '13+')),
    summary             TEXT,           -- public teaser
    objectives          TEXT,           -- pedagogic objectives (shown post-session)
    theoretical_content TEXT,           -- theoretical part (shown post-session)
    recipe              TEXT,           -- practical recipe (shown post-session)
    is_private          BOOLEAN         NOT NULL DEFAULT FALSE,
    created_at          TIMESTAMPTZ     NOT NULL DEFAULT NOW(),
    deleted_at          TIMESTAMPTZ,
    CONSTRAINT remaining_lte_max CHECK (remaining_seats <= max_attendees)
);

-- Session allowances (users allowed to register for private sessions) ----
CREATE TABLE IF NOT EXISTS session_allowances (
    id          SERIAL PRIMARY KEY,
    session_id  INTEGER     NOT NULL REFERENCES sessions(id) ON DELETE CASCADE,
    user_id     INTEGER     NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE (session_id, user_id)
);

-- Session media (photos, post-session) ------------------------
CREATE TABLE IF NOT EXISTS session_media (
    id          SERIAL PRIMARY KEY,
    session_id  INTEGER     NOT NULL REFERENCES sessions(id) ON DELETE CASCADE,
    filename    VARCHAR(255) NOT NULL,
    is_private  BOOLEAN     NOT NULL DEFAULT TRUE,  -- requires photo_consent
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Bookings ----------------------------------------------------
CREATE TABLE IF NOT EXISTS bookings (
    id                  SERIAL PRIMARY KEY,
    user_id             INTEGER     NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    session_id          INTEGER     NOT NULL REFERENCES sessions(id) ON DELETE RESTRICT,
    status              VARCHAR(30) NOT NULL DEFAULT 'pending'
                            CHECK (status IN ('pending', 'confirmed', 'attended', 'absent', 'credited', 'cancelled')),
    payment_intent_id   VARCHAR(255),           -- Stripe PaymentIntent id
    paid_at             TIMESTAMPTZ,
    used_credit         BOOLEAN     NOT NULL DEFAULT FALSE,
    confirmed_by_admin  BOOLEAN     NOT NULL DEFAULT FALSE,
    child_first_name    VARCHAR(100),           -- first name of the child attending
    child_last_name     VARCHAR(100),           -- last name of the child attending
    child_age           INTEGER,                -- age of the child (may differ from session age category)
    child_allergies     TEXT,                   -- food allergies (optional)
    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE (user_id, session_id)
);

-- Credits -----------------------------------------------------
CREATE TABLE IF NOT EXISTS credits (
    id              SERIAL PRIMARY KEY,
    user_id         INTEGER     NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    booking_id      INTEGER     REFERENCES bookings(id) ON DELETE SET NULL,
    reason          TEXT,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    used_at         TIMESTAMPTZ,
    used_booking_id INTEGER     REFERENCES bookings(id) ON DELETE SET NULL
);

-- Basket items (sessions added to basket before checkout) ----
CREATE TABLE IF NOT EXISTS basket_items (
    id               SERIAL PRIMARY KEY,
    user_id          INTEGER      NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    session_id       INTEGER      NOT NULL REFERENCES sessions(id) ON DELETE CASCADE,
    child_first_name VARCHAR(100),
    child_last_name  VARCHAR(100),
    child_age        INTEGER,
    child_allergies  TEXT,
    created_at       TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    UNIQUE (user_id, session_id)
);

-- General messages (homepage news thread) --------------------
CREATE TABLE IF NOT EXISTS general_messages (
    id         SERIAL PRIMARY KEY,
    body       TEXT        NOT NULL,
    type       VARCHAR(20) NOT NULL DEFAULT 'info'
                   CHECK (type IN ('info', 'warning', 'danger', 'success')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    deleted_at TIMESTAMPTZ
);

-- Password reset tokens ---------------------------------------
CREATE TABLE IF NOT EXISTS password_resets (
    id          SERIAL PRIMARY KEY,
    user_id     INTEGER     NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token_hash  VARCHAR(255) NOT NULL UNIQUE,
    expires_at  TIMESTAMPTZ NOT NULL,
    used        BOOLEAN     NOT NULL DEFAULT FALSE,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
