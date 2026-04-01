-- Migration 2026-04-01_001: create promo_codes table and add promo columns to bookings

CREATE TABLE IF NOT EXISTS promo_codes (
    id              SERIAL PRIMARY KEY,
    code            VARCHAR(50)  NOT NULL,
    session_id      INTEGER      REFERENCES sessions(id) ON DELETE CASCADE,  -- NULL = valid for any session
    discount_cents  INTEGER      NOT NULL CHECK (discount_cents > 0),
    max_uses        INTEGER,                                                  -- NULL = unlimited
    used_count      INTEGER      NOT NULL DEFAULT 0,
    expires_at      TIMESTAMPTZ,
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    deleted_at      TIMESTAMPTZ,
    UNIQUE (code)
);

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_name = 'bookings' AND column_name = 'promo_code_id'
    ) THEN
        ALTER TABLE bookings ADD COLUMN promo_code_id INTEGER REFERENCES promo_codes(id) ON DELETE SET NULL;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_name = 'bookings' AND column_name = 'discount_cents'
    ) THEN
        ALTER TABLE bookings ADD COLUMN discount_cents INTEGER NOT NULL DEFAULT 0;
    END IF;
END $$;
