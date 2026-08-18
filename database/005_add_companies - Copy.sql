-- Migration 005: Companies master list
-- Stores registered companies that can be invited to job fairs

CREATE TABLE IF NOT EXISTS companies (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(255) NOT NULL,
    industry     VARCHAR(100),
    contact_person VARCHAR(255),
    email        VARCHAR(255),
    phone        VARCHAR(50),
    address      TEXT,
    status       ENUM('active','inactive') DEFAULT 'active',
    created_by   INT UNSIGNED,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample companies
INSERT IGNORE INTO companies (name, industry, contact_person, email, phone, address, status) VALUES
('ABC Recruitment Corp',    'Recruitment',       'Maria Santos',   'info@abcrecruitment.com',  '09171234567', 'Quezon City, Metro Manila',  'active'),
('TechHire Philippines',    'IT & Technology',   'Juan dela Cruz', 'hr@techhire.ph',           '09281234567', 'Makati City, Metro Manila',  'active'),
('BuildRight Manpower',     'Construction',      'Ana Reyes',      'contact@buildright.com',   '09391234567', 'Pasig City, Metro Manila',   'active'),
('CareFirst Staffing',      'Healthcare',        'Pedro Lim',      'info@carefirst.ph',        '09451234567', 'Taguig City, Metro Manila',  'active'),
('FastTrack BPO Solutions', 'BPO / Call Center', 'Rosa Garcia',    'hr@fasttrackbpo.com',      '09561234567', 'Cebu City, Cebu',            'active'),
('GreenField Agriculture',  'Agriculture',       'Carlos Mendoza', 'jobs@greenfield.ph',       '09671234567', 'Davao City, Davao del Sur',  'active'),
('Metro Retail Group',      'Retail',            'Linda Cruz',     'careers@metroretail.com',  '09781234567', 'Mandaluyong, Metro Manila',  'active'),
('Pacific Logistics Inc',   'Logistics',         'Ramon Torres',   'hr@pacificlogistics.ph',   '09891234567', 'Parañaque, Metro Manila',    'active');
