-- Migration 2026-03-29_001: create packs and pack_sessions tables

CREATE TABLE IF NOT EXISTS packs (
    id          SERIAL PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    description TEXT,
    price_cents INTEGER     NOT NULL CHECK (price_cents >= 0),
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    deleted_at  TIMESTAMPTZ
);

CREATE TABLE IF NOT EXISTS pack_sessions (
    pack_id    INTEGER NOT NULL REFERENCES packs(id) ON DELETE CASCADE,
    session_id INTEGER NOT NULL REFERENCES sessions(id) ON DELETE CASCADE,
    PRIMARY KEY (pack_id, session_id)
);
