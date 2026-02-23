-- Migration 2026-02-23_001: create general_messages table

CREATE TABLE IF NOT EXISTS general_messages (
    id         SERIAL PRIMARY KEY,
    body       TEXT        NOT NULL,
    type       VARCHAR(20) NOT NULL DEFAULT 'info'
                   CHECK (type IN ('info', 'warning', 'danger', 'success')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    deleted_at TIMESTAMPTZ
);
