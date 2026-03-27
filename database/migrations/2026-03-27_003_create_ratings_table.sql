-- Migration 2026-03-27_003: create ratings table

CREATE TABLE IF NOT EXISTS ratings (
    id           SERIAL PRIMARY KEY,
    booking_id   INTEGER      NOT NULL REFERENCES bookings(id) ON DELETE CASCADE,
    user_id      INTEGER      NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    session_id   INTEGER      NOT NULL REFERENCES sessions(id) ON DELETE CASCADE,
    stars        SMALLINT     NOT NULL CHECK (stars >= 0 AND stars <= 5),
    comment      VARCHAR(200),
    is_anonymous BOOLEAN      NOT NULL DEFAULT FALSE,
    created_at   TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    UNIQUE (user_id, session_id)
);
