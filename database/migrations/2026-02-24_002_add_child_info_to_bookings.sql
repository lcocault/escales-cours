-- Migration 2026-02-24_002: add child information columns to bookings

ALTER TABLE bookings ADD COLUMN IF NOT EXISTS child_first_name VARCHAR(100);
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS child_last_name  VARCHAR(100);
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS child_age        INTEGER;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS child_allergies  TEXT;
