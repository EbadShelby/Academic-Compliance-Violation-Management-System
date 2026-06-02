<?php

/**
 * App Configuration
 * Reads values from the .env file and exposes them as constants.
 */

// BASE_PATH is defined in the front controller before this file is loaded.
// Fallback: derive it from __DIR__ in case this file is ever included standalone.
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__, 2));
}

// Load .env only once — path derived from BASE_PATH so it's always correct.
$envPath = BASE_PATH . '/.env';

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
// BASE_PATH is already defined above (or by the front controller) — do not redefine it.
defined('APP_NAME') || define('APP_NAME', env('APP_NAME', 'Academic Compliance & Violation Management System'));
defined('APP_URL')  || define('APP_URL',  env('APP_URL',  'http://localhost'));
defined('APP_ENV')  || define('APP_ENV',  env('APP_ENV',  'production'));

// ── Session constants ────────────────────────────────────────────────────────
defined('SESSION_NAME')     || define('SESSION_NAME',     env('SESSION_NAME',     'techxp_session'));
defined('SESSION_LIFETIME') || define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', 120));

// ── Upload path ──────────────────────────────────────────────────────────────
defined('UPLOAD_PATH') || define('UPLOAD_PATH', BASE_PATH . '/storage/' . env('UPLOAD_PATH', 'uploads/evidence/'));

/**
 * Helper: get an environment variable with an optional default.
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    return ($value !== false && $value !== null) ? $value : $default;
}

// ── SMTP constants ───────────────────────────────────────────────────────────
defined('SMTP_HOST') || define('SMTP_HOST', env('SMTP_HOST', 'smtp.gmail.com'));
defined('SMTP_PORT') || define('SMTP_PORT', (int) env('SMTP_PORT', 587));
defined('SMTP_USER') || define('SMTP_USER', env('SMTP_USER', ''));
defined('SMTP_PASS') || define('SMTP_PASS', env('SMTP_PASS', ''));
