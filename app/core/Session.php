<?php

/**
 * Session Helper
 *
 * Manages PHP sessions with a consistent API.
 * Must be started before any output is sent.
 */

class Session
{
    private static bool $started = false;

    /**
     * Start the session (idempotent).
     */
    public static function start(): void
    {
        if (static::$started || session_status() === PHP_SESSION_ACTIVE) {
            static::$started = true;
            return;
        }

        session_name(SESSION_NAME);

        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME * 60,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
        static::$started = true;
    }

    // ── Data access ──────────────────────────────────────────────────────────

    public static function set(string $key, mixed $value): void
    {
        static::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        static::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        static::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        static::start();
        unset($_SESSION[$key]);
    }

    // ── Flash messages ───────────────────────────────────────────────────────

    /**
     * Store a flash message (read-once).
     */
    public static function flash(string $key, mixed $value): void
    {
        static::start();
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Read and remove a flash message.
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        static::start();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        static::start();
        return isset($_SESSION['_flash'][$key]);
    }

    // ── Auth shortcuts ───────────────────────────────────────────────────────

    /**
     * Store authenticated user data.
     */
    public static function login(array $user): void
    {
        static::start();
        session_regenerate_id(true);
        $_SESSION['auth_user'] = $user;
    }

    /**
     * Destroy the session (logout).
     */
    public static function logout(): void
    {
        static::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        static::$started = false;
    }

    /**
     * Return the logged-in user array or null.
     */
    public static function user(): ?array
    {
        static::start();
        return $_SESSION['auth_user'] ?? null;
    }

    /**
     * Check whether a user is authenticated.
     */
    public static function isLoggedIn(): bool
    {
        return static::user() !== null;
    }

    /**
     * Check if the logged-in user has a specific role.
     */
    public static function hasRole(string $role): bool
    {
        $user = static::user();
        return $user !== null && strtolower($user['role'] ?? '') === strtolower($role);
    }
}
