-- Migration 2026-03-06_001: create basket_items table

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
