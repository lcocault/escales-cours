-- Migration 2026-03-27_002: add '3-12' to the age_category CHECK constraint on sessions

ALTER TABLE sessions
    DROP CONSTRAINT IF EXISTS sessions_age_category_check;

ALTER TABLE sessions
    ADD CONSTRAINT sessions_age_category_check
        CHECK (age_category IN ('3-5', '3-10', '3-12', '6-12', '13+'));
