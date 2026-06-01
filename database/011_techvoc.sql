-- Migration 011: TECH-VOC Supervisor role + classes + students

-- ─── Add role ─────────────────────────────────────────────────────────────────
-- (users.role ENUM needs to be extended)
ALTER TABLE users MODIFY COLUMN role ENUM(
    'admin',
    'supervising_labor',
    'barangay_captain',
    'secretary',
    'agency',
    'applicant',
    'techvoc_supervisor'
) NULL;

-- ─── TECH-VOC Classes ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS techvoc_classes (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,          -- e.g. "Welding Class", "Electrical Class"
    description  TEXT,
    schedule     VARCHAR(100) DEFAULT 'Every Sunday',
    duration     VARCHAR(50)  DEFAULT '6 months',
    start_date   DATE,
    end_date     DATE,
    status       ENUM('active','completed','cancelled') DEFAULT 'active',
    created_by   INT UNSIGNED,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── TECH-VOC Students ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS techvoc_students (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    techvoc_class_id INT UNSIGNED NOT NULL,
    lastname        VARCHAR(100) NOT NULL,
    firstname       VARCHAR(100) NOT NULL,
    middlename      VARCHAR(100),
    age             TINYINT UNSIGNED,
    gender          ENUM('male','female','other'),
    address         TEXT,
    contact_number  VARCHAR(20),
    email           VARCHAR(255),
    status          ENUM('active','dropped','completed') DEFAULT 'active',
    enrolled_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (techvoc_class_id) REFERENCES techvoc_classes(id) ON DELETE CASCADE,
    INDEX idx_class (techvoc_class_id),
    INDEX idx_status (status),
    INDEX idx_lastname (lastname)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Attendance (Sunday sessions) ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS techvoc_attendance (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    techvoc_class_id INT UNSIGNED NOT NULL,
    student_id      INT UNSIGNED NOT NULL,
    session_date    DATE NOT NULL,
    status          ENUM('present','absent','late','excused') DEFAULT 'present',
    notes           TEXT,
    recorded_by     INT UNSIGNED,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (techvoc_class_id) REFERENCES techvoc_classes(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES techvoc_students(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_attendance (student_id, session_date),
    INDEX idx_class_date (techvoc_class_id, session_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Seed: default classes ────────────────────────────────────────────────────
INSERT IGNORE INTO techvoc_classes (name, description, schedule, duration, start_date, end_date, status)
VALUES
(
    'Welding Class',
    'Basic and intermediate welding techniques including SMAW, GMAW, and GTAW. Students will learn metal fabrication, safety procedures, and welding standards.',
    'Every Sunday, 8:00 AM – 5:00 PM',
    '6 months',
    DATE_FORMAT(CURDATE(), '%Y-%m-01'),
    DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 6 MONTH),
    'active'
),
(
    'Electrical Class',
    'Residential and commercial electrical installation, wiring, circuit analysis, and electrical safety. Covers TESDA NC II competencies.',
    'Every Sunday, 8:00 AM – 5:00 PM',
    '6 months',
    DATE_FORMAT(CURDATE(), '%Y-%m-01'),
    DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 6 MONTH),
    'active'
);
