-- Migration 006: Add company_id FK to participating_agencies
ALTER TABLE participating_agencies
    ADD COLUMN IF NOT EXISTS company_id INT UNSIGNED NULL AFTER job_fair_request_id,
    ADD INDEX IF NOT EXISTS idx_company (company_id);
