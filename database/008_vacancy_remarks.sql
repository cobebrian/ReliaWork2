-- Migration 008: Add remarks/review fields to job_vacancies + notifications table

ALTER TABLE job_vacancies
    ADD COLUMN IF NOT EXISTS remarks     TEXT NULL AFTER qualifications,
    ADD COLUMN IF NOT EXISTS reviewed_by INT UNSIGNED NULL AFTER remarks,
    ADD COLUMN IF NOT EXISTS reviewed_at TIMESTAMP NULL AFTER reviewed_by,
    ADD COLUMN IF NOT EXISTS submitted_by INT UNSIGNED NULL AFTER reviewed_at;

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    type        VARCHAR(50) NOT NULL,
    title       VARCHAR(255) NOT NULL,
    message     TEXT,
    link        VARCHAR(500),
    is_read     BOOLEAN DEFAULT FALSE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
