-- Migration 020: Extend job_fair_reports for remarks, status, submission history

-- 1. Extend job_fair_reports with new columns
ALTER TABLE job_fair_reports
    ADD COLUMN IF NOT EXISTS overall_remarks       TEXT NULL AFTER notes,
    ADD COLUMN IF NOT EXISTS observations          TEXT NULL AFTER overall_remarks,
    ADD COLUMN IF NOT EXISTS recommendations       TEXT NULL AFTER observations,
    ADD COLUMN IF NOT EXISTS report_status
        ENUM('draft','submitted','reviewed') NOT NULL DEFAULT 'draft' AFTER recommendations,
    ADD COLUMN IF NOT EXISTS submitted_at          TIMESTAMP NULL AFTER report_status,
    ADD COLUMN IF NOT EXISTS submitted_to          INT UNSIGNED NULL AFTER submitted_at,
    ADD COLUMN IF NOT EXISTS reviewed_at           TIMESTAMP NULL AFTER submitted_to,
    ADD COLUMN IF NOT EXISTS reviewed_by           INT UNSIGNED NULL AFTER reviewed_at,
    ADD COLUMN IF NOT EXISTS reviewer_remarks      TEXT NULL AFTER reviewed_by,
    -- Extended hiring stats columns
    ADD COLUMN IF NOT EXISTS total_qualified       INT UNSIGNED DEFAULT 0 AFTER total_not_hired,
    ADD COLUMN IF NOT EXISTS total_waitlisted      INT UNSIGNED DEFAULT 0 AFTER total_qualified,
    ADD COLUMN IF NOT EXISTS total_awaiting_reqs   INT UNSIGNED DEFAULT 0 AFTER total_waitlisted,
    ADD COLUMN IF NOT EXISTS total_scheduled       INT UNSIGNED DEFAULT 0 AFTER total_awaiting_reqs;

-- 2. Report submission history log
CREATE TABLE IF NOT EXISTS report_submission_history (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id       INT UNSIGNED NOT NULL,
    action          ENUM('generated','submitted','reviewed','remarked') NOT NULL,
    performed_by    INT UNSIGNED NOT NULL,
    remarks         TEXT NULL,
    performed_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id)    REFERENCES job_fair_reports(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_rsh_report (report_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
