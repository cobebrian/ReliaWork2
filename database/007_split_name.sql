-- Migration 007: Split name into lastname, firstname, middlename
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS lastname   VARCHAR(100) NULL AFTER name,
    ADD COLUMN IF NOT EXISTS firstname  VARCHAR(100) NULL AFTER lastname,
    ADD COLUMN IF NOT EXISTS middlename VARCHAR(100) NULL AFTER firstname;

-- Migrate existing name data: put full name into firstname for now
UPDATE users SET firstname = name WHERE firstname IS NULL AND name IS NOT NULL;
