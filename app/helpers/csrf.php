<?php

/**
 * CSRF Helper Functions
 * 
 * Provides session-based CSRF token generation and validation.
 */

/**
 * Generate and retrieve the CSRF token from the session.
 * If one does not exist, it creates a new one.
 *
 * @return string
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate a hidden HTML input field containing the CSRF token.
 *
 * @return string
 */
function csrf_field(): string
{
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Verify the provided CSRF token against the session token.
 *
 * @param string|null $token The token to verify (defaults to $_POST['csrf_token'])
 * @return bool True if valid, false otherwise.
 */
function verify_csrf_token(?string $token = null): bool
{
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? '';
    }

    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}
