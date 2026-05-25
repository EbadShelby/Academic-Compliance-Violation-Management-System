<?php

/**
 * Role Middleware
 *
 * Guards routes that require specific user roles.
 *
 * Usage inside a controller action:
 *
 *   // Allow only admins
 *   RoleMiddleware::allow('admin');
 *
 *   // Allow admins OR teachers
 *   RoleMiddleware::allow(['admin', 'teacher']);
 *
 * The middleware:
 *   1. Runs AuthMiddleware::handle() first (unauthenticated → /login).
 *   2. If authenticated but wrong role → 403 Forbidden page.
 */

class RoleMiddleware
{
    /**
     * Allow only users whose role slug matches one of the given roles.
     *
     * @param  string|string[] $roles  A single role slug or an array of slugs.
     * @return void
     */
    public static function allow(string|array $roles): void
    {
        // Must be logged in first
        AuthMiddleware::handle();

        $allowed      = array_map('strtolower', (array) $roles);
        $currentRole  = strtolower(Session::user()['role'] ?? '');

        if (!in_array($currentRole, $allowed, true)) {
            self::forbidden();
        }
    }

    /**
     * Deny users whose role matches the given roles (inverse of allow).
     *
     * @param  string|string[] $roles
     * @return void
     */
    public static function deny(string|array $roles): void
    {
        // Must be logged in first
        AuthMiddleware::handle();

        $denied      = array_map('strtolower', (array) $roles);
        $currentRole = strtolower(Session::user()['role'] ?? '');

        if (in_array($currentRole, $denied, true)) {
            self::forbidden();
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Render the 403 error page and stop execution.
     */
    private static function forbidden(): never
    {
        http_response_code(403);
        $errorView = BASE_PATH . '/views/errors/403.php';

        if (file_exists($errorView)) {
            require $errorView;
        } else {
            echo '<h1>403 — Forbidden</h1><p>You do not have permission to view this page.</p>';
        }
        exit;
    }
}
