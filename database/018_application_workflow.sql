-- Migration 018: Full application workflow rebuild

-- 1. Extend applications table
ALTER TABLE applications
    ADD COLUMN IF NOT EXISTS job_fair_post_id    INT UNSIGNED NULL AFTER job_vacancy_id,
    ADD COLUMN IF NOT EXISTS job_fair_request_id INT UNSIGNED NULL AFTER job_fair_post_id,
    ADD COLUMN IF NOT EXISTS validation_status
        ENUM('pending_validation','approved','rejected','resubmit')
        NOT NULL DEFAULT 'pending_validation' AFTER status,
    ADD COLUMN IF NOT EXISTS validated_by        INT UNSIGNED NULL AFTER validation_status,
    ADD COLUMN IF NOT EXISTS validated_at        TIMESTAMP NULL AFTER validated_by,
    ADD COLUMN IF NOT EXISTS validator_remarks   TEXT NULL AFTER validated_at,
    ADD COLUMN IF NOT EXISTS interview_id        INT UNSIGNED NULL AFTER validator_remarks,
    ADD COLUMN IF NOT EXISTS notes               TEXT NULL AFTER interview_id;

-- 2. Application documents (per-application uploads, separate from global applicant_documents)
CREATE TABLE IF NOT EXISTS application_documents (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id  INT UNSIGNED NOT NULL,
    applicant_id    INT UNSIGNED NOT NULL,
    doc_type        ENUM('resume','cv','diploma','certificate','other') NOT NULL,
    original_name   VARCHAR(255) NOT NULL,
    stored_name     VARCHAR(255) NOT NULL,
    file_path       VARCHAR(500) NOT NULL,
    file_size       INT UNSIGNED DEFAULT 0,
    mime_type       VARCHAR(100),
    uploaded_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (applicant_id)   REFERENCES applicants(id)   ON DELETE CASCADE,
    INDEX idx_appdoc_app (application_id),
    INDEX idx_appdoc_applicant (applicant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Update interview_questions rating to use Excellent/Good/Fair/Poor
ALTER TABLE interview_questions
    MODIFY COLUMN IF EXISTS answer_status
        ENUM('excellent','good','fair','poor','not_answered') NULL;

-- 4. Add default_questions flag and application link to interview_questions
ALTER TABLE interview_questions
    ADD COLUMN IF NOT EXISTS is_default   TINYINT(1) DEFAULT 0 AFTER sort_order,
    ADD COLUMN IF NOT EXISTS score        TINYINT UNSIGNED NULL AFTER is_default;

-- 5. Default interview questions table (reusable templates)
CREATE TABLE IF NOT EXISTS interview_question_templates (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question     TEXT NOT NULL,
    category     VARCHAR(100) DEFAULT 'general',
    is_active    TINYINT(1) DEFAULT 1,
    sort_order   TINYINT UNSIGNED DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default questions
INSERT IGNORE INTO interview_question_templates (id, question, category, sort_order) VALUES
(1,  'Tell me about yourself.',                                      'general', 1),
(2,  'Why do you want to work for this company?',                    'general', 2),
(3,  'What are your strengths?',                                     'general', 3),
(4,  'What are your weaknesses?',                                    'general', 4),
(5,  'Why should we hire you?',                                      'general', 5),
(6,  'Where do you see yourself in five years?',                     'general', 6),
(7,  'How do you handle pressure or stressful situations?',          'general', 7),
(8,  'Describe a difficult situation you handled successfully.',      'general', 8),
(9,  'What motivates you at work?',                                  'general', 9),
(10, 'Do you have any questions for us?',                            'general', 10);

-- 6. Extend interviews to link to application
ALTER TABLE interviews
    ADD COLUMN IF NOT EXISTS application_id INT UNSIGNED NULL AFTER job_vacancy_id,
    ADD COLUMN IF NOT EXISTS score_summary  TEXT NULL AFTER hiring_remarks;
