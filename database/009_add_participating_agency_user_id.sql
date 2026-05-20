-- Migration 009: Add user_id to participating_agencies and populate from emails
ALTER TABLE participating_agencies
  ADD COLUMN `user_id` INT UNSIGNED NULL AFTER `job_fair_request_id`;

-- Populate user_id where email matches existing users
UPDATE participating_agencies pa
JOIN users u ON u.email = pa.email
SET pa.user_id = u.id;

-- Add index and FK
ALTER TABLE participating_agencies
  ADD KEY `idx_pa_user_id` (`user_id`),
  ADD CONSTRAINT `fk_pa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
