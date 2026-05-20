-- Migration 010: Add remarks column to job_vacancies for supervising labor feedback
ALTER TABLE job_vacancies
  ADD COLUMN `remarks` LONGTEXT NULL AFTER `qualifications`,
  ADD COLUMN `remarks_by` INT UNSIGNED NULL AFTER `remarks`,
  ADD KEY `idx_jv_remarks_by` (`remarks_by`),
  ADD CONSTRAINT `fk_jv_remarks_by` FOREIGN KEY (`remarks_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
