-- Migration 2026-02-24_003: add phone2 column to users for second parent emergency contact
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS phone2 VARCHAR(30);
