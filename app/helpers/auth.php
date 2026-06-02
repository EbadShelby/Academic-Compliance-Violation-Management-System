<?php

/**
 * Authorization Helpers
 *
 * Lightweight, reusable functions for role-based access control.
 * These wrap the Session and middleware classes so controllers and
 * views only need a single, expressive function call.
 *
 * Usage in controllers:
 *   authorize('admin');               // single role
 *   authorize(['admin', 'teacher']);  // multiple allowed roles
 *
 * Usage in views (for conditional UI rendering):
 *   if (isAdmin()) { ... }
 *   if (isTeacher()) { ... }
 */

// ── Authentication checks ─────────────────────────────────────────────────────

/**
 * Return true if the current request has an authenticated user.
 */
function isLoggedIn(): bool
{
    return Session::isLoggedIn();
}

/**
 * Return the currently authenticated user array, or null if not logged in.
 */
function currentUser(): ?array
{
    return Session::user();
}

/**
 * Return the role slug of the current user (lowercase), or empty string.
 */
function currentRole(): string
{
    $user = Session::user();
    return strtolower($user['role'] ?? '');
}

// ── Role checks ───────────────────────────────────────────────────────────────

/**
 * Return true if the authenticated user has the 'admin' role.
 */
function isAdmin(): bool
{
    return Session::hasRole('admin');
}

/**
 * Return true if the authenticated user has the 'teacher' role.
 */
function isTeacher(): bool
{
    return Session::hasRole('teacher');
}

/**
 * Return true if the authenticated user has the 'student' role.
 */
function isStudent(): bool
{
    return Session::hasRole('student');
}

/**
 * Return true if the authenticated user has the 'registrar' role.
 */
function isRegistrar(): bool
{
    return Session::hasRole('registrar');
}

/**
 * Return true if the current user's role matches any of the given roles.
 *
 * @param string|string[] $roles  Role slug(s) to check against.
 */
function hasAnyRole(string|array $roles): bool
{
    $allowed     = array_map('strtolower', (array) $roles);
    $currentRole = currentRole();

    return $currentRole !== '' && in_array($currentRole, $allowed, true);
}

// ── Authorization guard ───────────────────────────────────────────────────────

/**
 * Authorize the current request against one or more allowed roles.
 *
 * This is the primary guard function to call at the top of protected
 * controller actions. It combines authentication + role checks in one call:
 *
 *   1. If the user is NOT logged in  → redirect to /login (flash error).
 *   2. If the user's role is NOT in  → render 403 Forbidden and exit.
 *      the allowed list
 *
 * Examples:
 *   authorize('admin');
 *   authorize(['admin', 'teacher']);
 *
 * @param string|string[] $roles  Allowed role slug(s).
 * @return void
 */
function authorize(string|array $roles): void
{
    RoleMiddleware::allow($roles);
}

/**
 * Require that the user is logged in (any role).
 *
 * Redirects guests to /login. Use this for pages that any authenticated
 * user may access regardless of role.
 *
 * @return void
 */
function requireAuth(): void
{
    AuthMiddleware::handle();
}

/**
 * Ensure the request is coming from a guest (unauthenticated).
 *
 * Redirects authenticated users to /dashboard. Use this to protect
 * login/register pages from already-logged-in users.
 *
 * @return void
 */
function requireGuest(): void
{
    AuthMiddleware::guest();
}
