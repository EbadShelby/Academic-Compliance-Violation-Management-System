<?php

/**
 * Auth Middleware
 *
 * Guards protected routes — any route that requires an authenticated user.
 *
 * Usage inside a controller action:
 *
 *   AuthMiddleware::handle();
 *
 * The middleware redirects unauthenticated requests to /login and exits.
 * No further code in the caller is executed after the redirect.
 */

class AuthMiddleware
{
    /**
     * Ensure the current user is authenticated.
     *
     * Call this at the top of any controller action that needs protection.
     *
     * @param  string $redirectTo  Override the redirect destination (default: /login).
     * @return void
     */
    public static function handle(string $redirectTo = ''): void
    {
        if (!Session::isLoggedIn()) {
            // Store the originally requested URI so we can send the user back
            // after a successful login (optional enhancement).
            Session::flash('error', 'Please log in to access that page.');

            $loginUrl = $redirectTo !== '' ? $redirectTo : APP_URL . '/login';
            header('Location: ' . $loginUrl);
            exit;
        }
    }

    /**
     * Ensure the request is NOT authenticated (e.g. protect login/register
     * pages from already-logged-in users).
     *
     * @param  string $redirectTo  Override the destination (default: /dashboard).
     * @return void
     */
    public static function guest(string $redirectTo = ''): void
    {
        if (Session::isLoggedIn()) {
            $dashUrl = $redirectTo !== '' ? $redirectTo : APP_URL . '/dashboard';
            header('Location: ' . $dashUrl);
            exit;
        }
    }
}
