-- ReliaWork2 Seed Data
-- Default admin password: Admin@123 (bcrypt cost 12)

USE `reliawork2_db`;

-- --------------------------------------------------------
-- Default Admin User
-- Password: Admin@123
-- --------------------------------------------------------
INSERT INTO `users` (`name`, `email`, `password`, `role`, `status`, `created_at`) VALUES
('System Administrator', 'admin@reliawork2.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'approved', NOW());

-- Note: The hash above is a placeholder. The setup.php script will generate the correct bcrypt hash.
-- Actual password: Admin@123

-- --------------------------------------------------------
-- Admin Profile
-- --------------------------------------------------------
INSERT INTO `profiles` (`user_id`, `phone`, `address`, `organization`, `position`, `created_at`) VALUES
(1, '09000000000', 'City Hall, Main Street', 'ReliaWork2 System', 'System Administrator', NOW());

-- --------------------------------------------------------
-- Sample Barangay Resources
-- --------------------------------------------------------
INSERT INTO `barangay_resources` (`name`, `description`, `quantity`, `unit`, `status`, `created_at`) VALUES
('Monobloc Chairs', 'White plastic monobloc chairs for events', 200, 'pieces', 'available', NOW()),
('Folding Tables', '6-foot folding tables for booths and registration', 50, 'pieces', 'available', NOW()),
('Event Tents', '10x10 ft canopy tents for outdoor coverage', 20, 'units', 'available', NOW()),
('PA System / Speakers', 'Public address system with microphone and amplifier', 5, 'sets', 'available', NOW()),
('Microphones', 'Wireless handheld microphones', 10, 'pieces', 'available', NOW());

-- --------------------------------------------------------
-- Sample Announcements
-- --------------------------------------------------------
INSERT INTO `announcements` (`title`, `content`, `type`, `status`, `created_by`, `created_at`) VALUES
(
  'Welcome to ReliaWork2 Job Fair System',
  'The ReliaWork2 Job Fair Management System is now live. This platform connects job seekers with employers through organized job fair events. Barangay captains can submit job fair requests, agencies can post vacancies, and applicants can browse and apply for jobs. Please register to get started.',
  'general',
  'published',
  1,
  NOW()
),
(
  'Upcoming Job Fair Registration Open',
  'Registration for the upcoming community job fair is now open. Qualified applicants are encouraged to register and complete their profiles. Participating companies will be posting vacancies soon. Make sure your profile is complete to increase your chances of getting hired.',
  'job_opportunity',
  'published',
  1,
  NOW()
),
(
  'Important: System Maintenance Notice',
  'The ReliaWork2 system will undergo scheduled maintenance. During this period, some features may be temporarily unavailable. We apologize for any inconvenience. Please save your work before the maintenance window begins.',
  'emergency',
  'published',
  1,
  NOW()
);

-- --------------------------------------------------------
-- Sample Schedule of Events
-- --------------------------------------------------------
INSERT INTO `schedule_of_events` (`title`, `event_date`, `event_time`, `venue`, `description`, `status`, `created_by`, `created_at`) VALUES
('Community Job Fair 2025 - Q1', DATE_ADD(CURDATE(), INTERVAL 30 DAY), '08:00:00', 'Barangay Hall Plaza', 'First quarter community job fair event', 'available', 1, NOW()),
('Skills Training & Job Fair', DATE_ADD(CURDATE(), INTERVAL 60 DAY), '09:00:00', 'Municipal Gymnasium', 'Combined skills training and job fair event', 'available', 1, NOW()),
('Annual Job Fair 2025', DATE_ADD(CURDATE(), INTERVAL 90 DAY), '08:00:00', 'City Sports Complex', 'Annual large-scale job fair event', 'available', 1, NOW());
