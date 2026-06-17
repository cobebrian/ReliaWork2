-- Migration 013: Add BEDO role + job_fair_posts table

-- Add bedo to role ENUM
ALTER TABLE users MODIFY COLUMN role ENUM(
    'admin',
    'supervising_labor',
    'barangay_captain',
    'secretary',
    'agency',
    'applicant',
    'techvoc_supervisor',
    'bedo'
) NULL;

-- Job Fair Posts created by BEDO for the landing page
CREATE TABLE IF NOT EXISTS job_fair_posts (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_fair_request_id  INT UNSIGNED NOT NULL,
    title                VARCHAR(255) NOT NULL,
    description          TEXT,
    venue                VARCHAR(255),
    event_date           DATE,
    event_time           VARCHAR(50),
    status               ENUM('draft','published','archived') DEFAULT 'draft',
    created_by           INT UNSIGNED,
    published_at         TIMESTAMP NULL,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_fair_request_id) REFERENCES job_fair_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_event_date (event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
