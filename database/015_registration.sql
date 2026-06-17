-- Migration 015: Full NSRP Form 1 registration + job fair registrations

-- Complete applicants table rebuild with all NSRP fields
ALTER TABLE applicants
    ADD COLUMN IF NOT EXISTS suffix             VARCHAR(20) NULL AFTER middlename,
    ADD COLUMN IF NOT EXISTS date_of_birth      DATE NULL AFTER suffix,
    ADD COLUMN IF NOT EXISTS place_of_birth     VARCHAR(255) NULL AFTER date_of_birth,
    ADD COLUMN IF NOT EXISTS sex                ENUM('male','female') NULL AFTER place_of_birth,
    ADD COLUMN IF NOT EXISTS religion           VARCHAR(100) NULL AFTER sex,
    ADD COLUMN IF NOT EXISTS civil_status       ENUM('single','married','separated','live_in','widowed') NULL AFTER religion,
    ADD COLUMN IF NOT EXISTS present_address    TEXT NULL AFTER civil_status,
    ADD COLUMN IF NOT EXISTS height             VARCHAR(20) NULL AFTER present_address,
    ADD COLUMN IF NOT EXISTS tin                VARCHAR(50) NULL AFTER height,
    ADD COLUMN IF NOT EXISTS email              VARCHAR(255) NULL AFTER tin,
    ADD COLUMN IF NOT EXISTS landline           VARCHAR(30) NULL AFTER email,
    ADD COLUMN IF NOT EXISTS cellphone          VARCHAR(30) NULL AFTER landline,
    ADD COLUMN IF NOT EXISTS disability         VARCHAR(100) NULL AFTER cellphone,
    ADD COLUMN IF NOT EXISTS employment_status  VARCHAR(100) NULL AFTER disability,
    ADD COLUMN IF NOT EXISTS actively_looking   TINYINT(1) DEFAULT 1 AFTER employment_status,
    ADD COLUMN IF NOT EXISTS willing_immediate  TINYINT(1) DEFAULT 1 AFTER actively_looking,
    ADD COLUMN IF NOT EXISTS is_4ps             TINYINT(1) DEFAULT 0 AFTER willing_immediate,
    ADD COLUMN IF NOT EXISTS household_id       VARCHAR(50) NULL AFTER is_4ps,
    ADD COLUMN IF NOT EXISTS preferred_occupation TEXT NULL AFTER household_id,
    ADD COLUMN IF NOT EXISTS preferred_location  TEXT NULL AFTER preferred_occupation,
    ADD COLUMN IF NOT EXISTS expected_salary     VARCHAR(100) NULL AFTER preferred_location,
    ADD COLUMN IF NOT EXISTS passport_no        VARCHAR(50) NULL AFTER expected_salary,
    ADD COLUMN IF NOT EXISTS educational_bg     TEXT NULL AFTER passport_no,
    ADD COLUMN IF NOT EXISTS trainings          TEXT NULL AFTER educational_bg,
    ADD COLUMN IF NOT EXISTS eligibility        TEXT NULL AFTER trainings,
    ADD COLUMN IF NOT EXISTS work_experience    TEXT NULL AFTER eligibility,
    ADD COLUMN IF NOT EXISTS other_skills       TEXT NULL AFTER work_experience;

-- Job fair registrations (online registration by job seeker)
CREATE TABLE IF NOT EXISTS job_fair_registrations (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_fair_post_id    INT UNSIGNED NOT NULL,
    applicant_id        INT UNSIGNED NOT NULL,
    user_id             INT UNSIGNED,
    registered_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status              ENUM('registered','attended','no_show') DEFAULT 'registered',
    FOREIGN KEY (job_fair_post_id) REFERENCES job_fair_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_reg (job_fair_post_id, applicant_id),
    INDEX idx_post (job_fair_post_id),
    INDEX idx_applicant (applicant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
