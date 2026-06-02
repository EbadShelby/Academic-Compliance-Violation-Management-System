-- =============================================================================
-- Academic Compliance & Violation Management System
-- Migration Phase 13: Password Resets
-- =============================================================================

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(191) NOT NULL,
    `token`      VARCHAR(255) NOT NULL COMMENT 'Hashed token',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP    NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_password_resets_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
