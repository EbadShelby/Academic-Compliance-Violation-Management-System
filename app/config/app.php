<?php

/**
 * App Configuration
 * Reads values from the .env file and exposes them as constants.
 */

// Load .env only once
$envPath = dirname(__DIR__, 2) . '/.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

// ── Application constants ────────────────────────────────────────────────────
define('APP_NAME',     env('APP_NAME',     'Academic Compliance & Violation Management System'));
define('APP_URL',      env('APP_URL',      'http://localhost'));
define('APP_ENV',      env('APP_ENV',      'production'));
define('BASE_PATH',    dirname(__DIR__, 2)); // project root

// ── Session constants ────────────────────────────────────────────────────────
define('SESSION_NAME',     env('SESSION_NAME',     'techxp_session'));
define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', 120));

// ── Upload path ──────────────────────────────────────────────────────────────
define('UPLOAD_PATH', BASE_PATH . '/storage/' . env('UPLOAD_PATH', 'uploads/evidence/'));

/**
 * Helper: get an environment variable with an optional default.
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    return ($value !== false && $value !== null) ? $value : $default;
}
