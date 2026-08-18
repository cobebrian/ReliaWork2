-- Migration 021: Organization-Based RBAC (parallel system)
-- Existing users/roles remain untouched.
-- New tables sit alongside and will gradually absorb role logic.

-- 1. Add 'normal_user' to the existing role ENUM (default for new registrations)
ALTER TABLE users MODIFY COLUMN role ENUM(
    'admin',
    'supervising_labor',
    'barangay_captain',
    'secretary',
    'agency',
    'applicant',
    'techvoc_supervisor',
    'bedo',
    'validating_officer',
    'reporting_officer',
    'normal_user'
) NULL DEFAULT NULL;

-- 2. Organizations (Barangay Halls)
CREATE TABLE IF NOT EXISTS organizations (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(200) NOT NULL,
    barangay        VARCHAR(200) NOT NULL,
    municipality    VARCHAR(200) NULL,
    province        VARCHAR(200) NULL,
    address         TEXT NULL,
    contact_email   VARCHAR(191) NULL,
    contact_phone   VARCHAR(30) NULL,
    status          ENUM('pending','active','suspended') NOT NULL DEFAULT 'active',
    created_by      INT UNSIGNED NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_org_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Organization Applications (Secretary applies to create a Barangay org)
CREATE TABLE IF NOT EXISTS organization_applications (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    applicant_user_id   INT UNSIGNED NOT NULL,
    org_name            VARCHAR(200) NOT NULL,
    barangay            VARCHAR(200) NOT NULL,
    municipality        VARCHAR(200) NULL,
    province            VARCHAR(200) NULL,
    address             TEXT NULL,
    contact_email       VARCHAR(191) NULL,
    contact_phone       VARCHAR(30) NULL,
    status              ENUM('pending','under_review','approved','rejected','needs_revision')
                        NOT NULL DEFAULT 'pending',
    reviewed_by         INT UNSIGNED NULL,
    reviewed_at         TIMESTAMP NULL,
    review_notes        TEXT NULL,
    organization_id     INT UNSIGNED NULL,    -- populated on approval
    submitted_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (applicant_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by)       REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (organization_id)   REFERENCES organizations(id) ON DELETE SET NULL,
    INDEX idx_oa_applicant (applicant_user_id),
    INDEX idx_oa_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Organization Application Documents
CREATE TABLE IF NOT EXISTS org_application_documents (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    org_application_id      INT UNSIGNED NOT NULL,
    doc_type                ENUM(
                                'barangay_form',
                                'appointment_proof',
                                'barangay_certification',
                                'valid_id',
                                'other'
                            ) NOT NULL,
    original_name           VARCHAR(255) NOT NULL,
    stored_name             VARCHAR(255) NOT NULL,
    file_path               VARCHAR(500) NOT NULL,
    file_size               INT UNSIGNED DEFAULT 0,
    mime_type               VARCHAR(100),
    uploaded_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (org_application_id) REFERENCES organization_applications(id) ON DELETE CASCADE,
    INDEX idx_oad_app (org_application_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Organization Roles (org-scoped roles)
CREATE TABLE IF NOT EXISTS org_roles (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(50) NOT NULL,       -- organization_admin, punong_barangay, bedo_officer, agency, member
    label           VARCHAR(100) NOT NULL,
    description     TEXT NULL,
    UNIQUE KEY uq_role_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default org roles
INSERT IGNORE INTO org_roles (id, name, label) VALUES
(1, 'organization_admin', 'Organization Admin (Secretary)'),
(2, 'punong_barangay',    'Punong Barangay'),
(3, 'bedo_officer',       'BEDO Officer'),
(4, 'agency',             'Agency'),
(5, 'member',             'Member');

-- 6. Organization Memberships (user ↔ org ↔ org_role)
CREATE TABLE IF NOT EXISTS organization_memberships (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    organization_id INT UNSIGNED NOT NULL,
    org_role_id     INT UNSIGNED NOT NULL,
    status          ENUM('active','inactive','invited','pending') NOT NULL DEFAULT 'active',
    invited_by      INT UNSIGNED NULL,
    joined_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_membership (user_id, organization_id),
    FOREIGN KEY (user_id)         REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (org_role_id)     REFERENCES org_roles(id) ON DELETE RESTRICT,
    FOREIGN KEY (invited_by)      REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_mem_org (organization_id),
    INDEX idx_mem_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Track organization application status history
CREATE TABLE IF NOT EXISTS org_application_history (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    org_application_id  INT UNSIGNED NOT NULL,
    from_status         VARCHAR(30) NULL,
    to_status           VARCHAR(30) NOT NULL,
    changed_by          INT UNSIGNED NULL,
    notes               TEXT NULL,
    changed_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (org_application_id) REFERENCES organization_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by)         REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
