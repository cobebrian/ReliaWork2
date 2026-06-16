-- Migration 012: Agency profile fields + resource confirmation

-- Add profile columns to users table for agency setup
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS agency_name     VARCHAR(200) NULL AFTER middlename,
    ADD COLUMN IF NOT EXISTS agency_location VARCHAR(255) NULL AFTER agency_name,
    ADD COLUMN IF NOT EXISTS profile_setup   TINYINT(1) DEFAULT 0 AFTER agency_location;

-- Add user_id FK to participating_agencies if not exists
ALTER TABLE participating_agencies
    ADD COLUMN IF NOT EXISTS user_id INT UNSIGNED NULL AFTER id;

-- Resource confirmation status for secretary
ALTER TABLE resource_allocations
    ADD COLUMN IF NOT EXISTS confirmed_by   INT UNSIGNED NULL AFTER allocated_by,
    ADD COLUMN IF NOT EXISTS confirmed_at   TIMESTAMP NULL AFTER confirmed_by;
