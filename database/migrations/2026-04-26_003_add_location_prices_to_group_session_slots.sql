-- Migration 2026-04-26_003: replace single price_per_child_cents with separate home/escales prices on group_session_slots

ALTER TABLE group_session_slots
    ADD COLUMN IF NOT EXISTS price_per_child_home_cents    INTEGER NOT NULL DEFAULT 3000
        CHECK (price_per_child_home_cents >= 0),
    ADD COLUMN IF NOT EXISTS price_per_child_escales_cents INTEGER NOT NULL DEFAULT 3500
        CHECK (price_per_child_escales_cents >= 0);

-- Copy existing price into both new columns for rows that still have the old default values
DO $$
BEGIN
    UPDATE group_session_slots
    SET price_per_child_home_cents    = price_per_child_cents,
        price_per_child_escales_cents = price_per_child_cents
    WHERE price_per_child_home_cents = 3000
      AND price_per_child_escales_cents = 3500;
END $$;

ALTER TABLE group_session_slots
    DROP COLUMN IF EXISTS price_per_child_cents;
