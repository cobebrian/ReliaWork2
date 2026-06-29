-- Migration 019: Extended hiring workflow
-- Statuses: qualified_for_contact | waitlisted | not_qualified
-- Employment requirements, first-day scheduling, in-app messaging, final hiring

-- 1. Update interviews hiring_outcome to new statuses
ALTER TABLE interviews
    MODIFY COLUMN hiring_outcome
        ENUM('pending','qualified_for_contact','waitlisted','not_qualified','hired') DEFAULT 'pending';

-- 2. Extend applications with full hiring lifecycle status
ALTER TABLE applications
    MODIFY COLUMN status
        ENUM('pending','shortlisted','qualified_for_contact','waitlisted',
             'awaiting_requirements','requirements_submitted','first_day_scheduled',
             'hired','not_qualified','rejected') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS hired_at          TIMESTAMP NULL AFTER notes,
    ADD COLUMN IF NOT EXISTS first_day_date    DATE NULL AFTER hired_at,
    ADD COLUMN IF NOT EXISTS first_day_time    TIME NULL AFTER first_day_date,
    ADD COLUMN IF NOT EXISTS first_day_location VARCHAR(255) NULL AFTER first_day_time,
    ADD COLUMN IF NOT EXISTS first_day_notes   TEXT NULL AFTER first_day_location,
    ADD COLUMN IF NOT EXISTS scheduled_at      TIMESTAMP NULL AFTER first_day_notes;

-- 3. Employment requirement documents (SSS/PhilHealth/TIN uploads after qualification)
CREATE TABLE IF NOT EXISTS employment_documents (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id  INT UNSIGNED NOT NULL,
    applicant_id    INT UNSIGNED NOT NULL,
    doc_type        ENUM('sss_id','philhealth_id','tin','nbi_clearance','medical','other') NOT NULL,
    original_name   VARCHAR(255) NOT NULL,
    stored_name     VARCHAR(255) NOT NULL,
    file_path       VARCHAR(500) NOT NULL,
    file_size       INT UNSIGNED DEFAULT 0,
    mime_type       VARCHAR(100),
    uploaded_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (applicant_id)   REFERENCES applicants(id)   ON DELETE CASCADE,
    INDEX idx_emp_doc_app (application_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. In-app messaging between agency and applicant
CREATE TABLE IF NOT EXISTS messages (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id  INT UNSIGNED NOT NULL,
    sender_id       INT UNSIGNED NOT NULL,
    sender_role     ENUM('agency','applicant','system') NOT NULL DEFAULT 'agency',
    message         TEXT NOT NULL,
    is_read         TINYINT(1) DEFAULT 0,
    sent_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id)      REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_msg_app (application_id),
    INDEX idx_msg_sender (sender_id),
    INDEX idx_msg_unread (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Application status history (audit trail)
CREATE TABLE IF NOT EXISTS application_status_history (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id  INT UNSIGNED NOT NULL,
    from_status     VARCHAR(50),
    to_status       VARCHAR(50) NOT NULL,
    changed_by      INT UNSIGNED NULL,
    remarks         TEXT NULL,
    changed_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by)     REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_history_app (application_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
