-- Migration 2026-02-24_001: add age_category column to sessions

ALTER TABLE sessions
    ADD COLUMN IF NOT EXISTS age_category VARCHAR(10) NOT NULL DEFAULT '6-12'
        CHECK (age_category IN ('3-5', '6-12', '13+'));
