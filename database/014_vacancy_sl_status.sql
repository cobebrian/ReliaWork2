-- Migration 014: Add sl_status to job_vacancies for SL processing

ALTER TABLE job_vacancies
    ADD COLUMN IF NOT EXISTS sl_status ENUM('pending','accepted','rejected') DEFAULT 'pending' AFTER status,
    ADD COLUMN IF NOT EXISTS sl_remarks TEXT NULL AFTER sl_status,
    ADD COLUMN IF NOT EXISTS sl_processed_by INT UNSIGNED NULL AFTER sl_remarks,
    ADD COLUMN IF NOT EXISTS sl_processed_at TIMESTAMP NULL AFTER sl_processed_by,
    ADD INDEX IF NOT EXISTS idx_sl_status (sl_status);
