-- Migration 016: Validation workflow + interview system

-- 1. Add validating_officer to users role ENUM
ALTER TABLE users MODIFY COLUMN role ENUM(
    'admin',
    'supervising_labor',
    'barangay_captain',
    'secretary',
    'agency',
    'applicant',
    'techvoc_supervisor',
    'bedo',
    'validating_officer'
) NULL;

-- 2. Add validation_status to applicants table
ALTER TABLE applicants
    ADD COLUMN IF NOT EXISTS validation_status
        ENUM('not_submitted','pending','approved','rejected','resubmit')
        NOT NULL DEFAULT 'not_submitted' AFTER updated_at,
    ADD COLUMN IF NOT EXISTS validated_by     INT UNSIGNED NULL AFTER validation_status,
    ADD COLUMN IF NOT EXISTS validated_at     TIMESTAMP NULL AFTER validated_by,
    ADD COLUMN IF NOT EXISTS validator_remarks TEXT NULL AFTER validated_at;

-- 3. Applicant uploaded documents
CREATE TABLE IF NOT EXISTS applicant_documents (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    applicant_id    INT UNSIGNED NOT NULL,
    doc_type        ENUM('resume','cv','diploma','certificate','other') NOT NULL,
    original_name   VARCHAR(255) NOT NULL,
    stored_name     VARCHAR(255) NOT NULL,
    file_path       VARCHAR(500) NOT NULL,
    file_size       INT UNSIGNED DEFAULT 0,
    mime_type       VARCHAR(100),
    uploaded_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE,
    INDEX idx_doc_applicant (applicant_id),
    INDEX idx_doc_type (doc_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Interviews created by agency after validation approval
CREATE TABLE IF NOT EXISTS interviews (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    applicant_id        INT UNSIGNED NOT NULL,
    agency_id           INT UNSIGNED NOT NULL,   -- participating_agencies.id
    job_vacancy_id      INT UNSIGNED NULL,
    scheduled_at        DATETIME NULL,
    status              ENUM('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
    overall_remarks     TEXT NULL,
    completed_at        TIMESTAMP NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE,
    FOREIGN KEY (agency_id)    REFERENCES participating_agencies(id) ON DELETE CASCADE,
    FOREIGN KEY (job_vacancy_id) REFERENCES job_vacancies(id) ON DELETE SET NULL,
    INDEX idx_interview_applicant (applicant_id),
    INDEX idx_interview_agency (agency_id),
    INDEX idx_interview_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Interview questions + evaluation
CREATE TABLE IF NOT EXISTS interview_questions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    interview_id    INT UNSIGNED NOT NULL,
    question_text   TEXT NOT NULL,
    answer_status   ENUM('answered','needs_improvement','not_answered') NULL,
    remarks         TEXT NULL,
    sort_order      TINYINT UNSIGNED DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (interview_id) REFERENCES interviews(id) ON DELETE CASCADE,
    INDEX idx_iq_interview (interview_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
