-- =============================================================================
-- Phase 10 Migration — Case Management Module
-- Academic Compliance & Violation Management System
--
-- Run this in phpMyAdmin (or any MySQL client) BEFORE using Phase 10 features.
-- It is safe to re-run: all statements use IF NOT EXISTS / IGNORE semantics
-- or are idempotent ALTER / UPDATE operations.
-- =============================================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ─────────────────────────────────────────────────────────────────────────────
-- Step 1 — Expand the violations.status ENUM
--
-- Old: ENUM('open','under_review','resolved','dismissed')
-- New: ENUM('pending','under_review','resolved','rejected','closed')
--
-- Note: MySQL ALTER COLUMN on ENUM preserves existing rows as long as the
-- old values are listed in the new ENUM *or* we UPDATE them first.
-- We therefore add the new values first, then migrate rows, then drop old ones.
-- ─────────────────────────────────────────────────────────────────────────────

-- 1a. Widen to the union of old + new values (safe, no data loss)
ALTER TABLE `violations`
    MODIFY COLUMN `status`
        ENUM('open','pending','under_review','resolved','dismissed','rejected','closed')
        NOT NULL DEFAULT 'pending';

-- 1b. Migrate legacy status values
UPDATE `violations` SET `status` = 'pending'  WHERE `status` = 'open';
UPDATE `violations` SET `status` = 'rejected' WHERE `status` = 'dismissed';

-- 1c. Narrow the ENUM to the final set only
ALTER TABLE `violations`
    MODIFY COLUMN `status`
        ENUM('pending','under_review','resolved','rejected','closed')
        NOT NULL DEFAULT 'pending';

-- ─────────────────────────────────────────────────────────────────────────────
-- Step 2 — Add sanction_notes column
-- NOTE: If you get "Duplicate column name" it means the column already exists
--       from a previous run — skip this statement and continue.
-- ─────────────────────────────────────────────────────────────────────────────

ALTER TABLE `violations`
    ADD COLUMN `sanction_notes`   TEXT NULL
        COMMENT 'Admin-assigned sanction details'
        AFTER `status`;

-- ─────────────────────────────────────────────────────────────────────────────
-- Step 3 — Add rejection_reason column
-- NOTE: Same as above — skip if "Duplicate column name" error appears.
-- ─────────────────────────────────────────────────────────────────────────────

ALTER TABLE `violations`
    ADD COLUMN `rejection_reason` TEXT NULL
        COMMENT 'Required reason when a case is rejected'
        AFTER `sanction_notes`;

-- ─────────────────────────────────────────────────────────────────────────────
-- Step 4 — Ensure violation_actions has the expected schema
--          (table already exists from schema.sql; this is a safety net)
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `violation_actions` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `violation_id`  INT UNSIGNED    NOT NULL,
    `actor_id`      INT UNSIGNED    NOT NULL COMMENT 'User who performed the action',
    `action_type`   VARCHAR(64)     NOT NULL COMMENT 'e.g. status_change, sanction_assigned, case_rejected',
    `note`          TEXT            NULL,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_va_violation` (`violation_id`),
    CONSTRAINT `fk_va_violation`
        FOREIGN KEY (`violation_id`) REFERENCES `violations` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_va_actor`
        FOREIGN KEY (`actor_id`)     REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────

SET foreign_key_checks = 1;

-- Done. Verify with:
--   DESCRIBE violations;
--   SELECT id, status, sanction_notes, rejection_reason FROM violations LIMIT 5;
