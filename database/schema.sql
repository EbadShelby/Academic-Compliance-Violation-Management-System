-- =============================================================================
-- Academic Compliance & Violation Management System
-- Database Schema — v1.0
-- Engine: InnoDB | Charset: utf8mb4
-- =============================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- ─────────────────────────────────────────────────────────────────────────────
-- 1. ROLES
--    slug is the machine-readable identifier used in session + middleware.
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `roles` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(64)     NOT NULL COMMENT 'Display name e.g. Administrator',
    `slug`        VARCHAR(32)     NOT NULL UNIQUE COMMENT 'Machine key e.g. admin',
    `description` VARCHAR(255)    NULL,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default roles
INSERT IGNORE INTO `roles` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Administrator', 'admin',   'Full access to all features and settings'),
(2, 'Teacher',       'teacher', 'Can file and manage violation reports'),
(3, 'Student',       'student', 'Can view their own violation records only'),
(4, 'Registrar',     'registrar', 'Manages and processes academic violations');

-- ─────────────────────────────────────────────────────────────────────────────
-- 2. USERS
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `role_id`      INT UNSIGNED    NOT NULL DEFAULT 3,
    `first_name`   VARCHAR(100)    NOT NULL,
    `last_name`    VARCHAR(100)    NOT NULL,
    `email`        VARCHAR(191)    NOT NULL UNIQUE,
    `password`     VARCHAR(255)    NOT NULL COMMENT 'bcrypt hash — never plain text',
    `student_id`   VARCHAR(64)     NULL UNIQUE COMMENT 'Student ID number (null for staff)',
    `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_users_email`   (`email`),
    KEY `idx_users_role_id` (`role_id`),
    CONSTRAINT `fk_users_role`
        FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- Seed: Default admin account
-- Password: Admin@1234  (bcrypt hash below — CHANGE AFTER FIRST LOGIN)
-- Generate a new hash with: password_hash('YourPassword', PASSWORD_DEFAULT)
-- ─────────────────────────────────────────────────────────────────────────────
INSERT IGNORE INTO `users`
    (`id`, `role_id`, `first_name`, `last_name`, `email`, `password`, `is_active`)
VALUES (
    1, 1, 'System', 'Administrator', 'admin@acvms.edu',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    -- ↑ Hash of "Admin@1234" — replace with your own
    1
),
(
    2, 4, 'System', 'Registrar', 'registrar@acvms.edu',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    -- ↑ Hash of "Registrar@1234" (reusing the same hash for testing)
    1
);

-- ─────────────────────────────────────────────────────────────────────────────
-- 3. VIOLATIONS
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `violations` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `student_id`    INT UNSIGNED    NOT NULL COMMENT 'FK → users.id (the student)',
    `reported_by`   INT UNSIGNED    NOT NULL COMMENT 'FK → users.id (teacher/admin)',
    `type`          VARCHAR(100)    NOT NULL,
    `description`   TEXT            NOT NULL,
    `severity`      ENUM('minor','moderate','major','critical') NOT NULL DEFAULT 'minor',
    `status`        ENUM('open','under_review','resolved','dismissed') NOT NULL DEFAULT 'open',
    `incident_date` DATE            NOT NULL,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_violations_student`   (`student_id`),
    KEY `idx_violations_reporter`  (`reported_by`),
    KEY `idx_violations_status`    (`status`),
    CONSTRAINT `fk_violations_student`
        FOREIGN KEY (`student_id`)  REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_violations_reporter`
        FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 4. VIOLATION ACTIONS  (comments / follow-up actions on a violation)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `violation_actions` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `violation_id`  INT UNSIGNED    NOT NULL,
    `actor_id`      INT UNSIGNED    NOT NULL COMMENT 'User who performed the action',
    `action_type`   VARCHAR(64)     NOT NULL COMMENT 'e.g. comment, status_change, warning_issued',
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
-- 5. EVIDENCE FILES  (attachments for a violation)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `evidence_files` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `violation_id`  INT UNSIGNED    NOT NULL,
    `uploaded_by`   INT UNSIGNED    NOT NULL,
    `file_name`     VARCHAR(255)    NOT NULL,
    `file_path`     VARCHAR(500)    NOT NULL,
    `mime_type`     VARCHAR(100)    NULL,
    `file_size`     INT UNSIGNED    NULL COMMENT 'Bytes',
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ef_violation` (`violation_id`),
    CONSTRAINT `fk_ef_violation`
        FOREIGN KEY (`violation_id`) REFERENCES `violations` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_ef_uploader`
        FOREIGN KEY (`uploaded_by`)  REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 6. AUDIT LOGS  (immutable record of all system events)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED    NULL COMMENT 'NULL = system action',
    `action`      VARCHAR(100)    NOT NULL COMMENT 'e.g. user.login, violation.created',
    `target_type` VARCHAR(64)     NULL COMMENT 'e.g. User, Violation',
    `target_id`   INT UNSIGNED    NULL,
    `detail`      JSON            NULL,
    `ip_address`  VARCHAR(45)     NULL,
    `user_agent`  VARCHAR(500)    NULL,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_audit_user`   (`user_id`),
    KEY `idx_audit_action` (`action`),
    KEY `idx_audit_date`   (`created_at`),
    CONSTRAINT `fk_audit_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 7. PASSWORD RESETS
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(191) NOT NULL,
    `token`      VARCHAR(255) NOT NULL COMMENT 'Hashed token',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP    NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_password_resets_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;
