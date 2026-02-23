-- Migration 2026-02-23_002: add status column to sessions

ALTER TABLE sessions
    ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'pending'
        CHECK (status IN ('pending', 'confirmed', 'cancelled'));
