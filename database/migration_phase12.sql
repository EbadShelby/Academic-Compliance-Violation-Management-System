-- =============================================================================
-- Phase 12 — Notification System Migration
-- =============================================================================
-- Run this script once against your MySQL database before deploying Phase 12.
-- It is safe to run on an existing database as long as the users table exists.
-- =============================================================================

CREATE TABLE IF NOT EXISTS notifications (
    id              INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED  NOT NULL,
    title           VARCHAR(255)  NOT NULL,
    message         TEXT          NOT NULL,
    type            ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
    is_read         TINYINT(1)    NOT NULL DEFAULT 0,
    reference_id    INT UNSIGNED  NULL,
    reference_table VARCHAR(64)   NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_notifications_user_read (user_id, is_read),
    INDEX idx_notifications_created   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
