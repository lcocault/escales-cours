-- Migration 2026-04-02_001: allow external URL photos in session_media
--
-- Makes filename nullable and adds an external_url column so that a photo
-- can be either a locally-uploaded file (filename set, external_url NULL) or
-- a publicly-accessible URL (external_url set, filename NULL).

ALTER TABLE session_media
    ALTER COLUMN filename DROP NOT NULL;

ALTER TABLE session_media
    ADD COLUMN IF NOT EXISTS external_url TEXT NULL;

-- Exactly one of filename / external_url must be non-null (checked on insert/update)
ALTER TABLE session_media
    ADD CONSTRAINT chk_session_media_source
    CHECK (
        (filename IS NOT NULL AND external_url IS NULL)
        OR
        (filename IS NULL AND external_url IS NOT NULL)
    );
