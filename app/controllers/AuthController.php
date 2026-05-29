<?php

/**
 * Auth Controller
 *
 * Handles login, logout, and session establishment.
 * Every authentication event is logged to the audit trail.
 */

class AuthController extends Controller
{
    // ── Show Login Form ───────────────────────────────────────────────────────

    /**
     * GET /login
     *
     * Show the login page. Redirect already-authenticated users to dashboard.
     */
    public function showLogin(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect(APP_URL . '/dashboard');
        }

        $this->view('auth.login', [
            'title'   => 'Login — ' . APP_NAME,
            'error'   => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
            'old'     => Session::getFlash('old') ?? [],
        ], '');  // No layout — login page has its own full HTML shell
    }

    // ── Process Login ─────────────────────────────────────────────────────────

    /**
     * POST /login
     *
     * Validate credentials and start an authenticated session.
     */
    public function login(): void
    {
        // ── 1. Sanitise inputs ───────────────────────────────────────────────
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        // Preserve entered email for repopulating the form on failure
        Session::flash('old', ['email' => $email]);

        // ── 2. Basic validation ──────────────────────────────────────────────
        if ($email === '' || $password === '') {
            Session::flash('error', 'Email and password are required.');
            $this->redirect(APP_URL . '/login');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please enter a valid email address.');
            $this->redirect(APP_URL . '/login');
        }

        // ── 3. Look up user ──────────────────────────────────────────────────
        /** @var User $userModel */
        $userModel = $this->model('User');
        $user      = $userModel->findByEmail($email);

        // ── 4. Verify credentials ────────────────────────────────────────────
        // Use a generic message for both "user not found" and "wrong password"
        // to prevent user enumeration.
        if (!$user || !$userModel->verifyPassword($password, $user['password'])) {
            // Log failed login attempt (user_id is null — not authenticated)
            logAction('auth.login_failed', 'User', null, ['email' => $email], null);

            Session::flash('error', 'Invalid email or password. Please try again.');
            $this->redirect(APP_URL . '/login');
        }

        // ── 5. Check account status ──────────────────────────────────────────
        if (isset($user['is_active']) && (int) $user['is_active'] === 0) {
            logAction('auth.login_blocked', 'User', (int) $user['id'], [
                'reason' => 'account_deactivated',
                'email'  => $email,
            ], (int) $user['id']);

            Session::flash('error', 'Your account has been deactivated. Please contact an administrator.');
            $this->redirect(APP_URL . '/login');
        }

        // ── 6. Establish session ─────────────────────────────────────────────
        Session::login([
            'id'      => (int) $user['id'],
            'role_id' => (int) $user['role_id'],
            'role'    => $user['role'] ?? 'student',   // slug from roles table
            'email'   => $user['email'],
            'name'    => trim($user['first_name'] . ' ' . $user['last_name']),
        ]);

        // ── 7. Log successful login ──────────────────────────────────────────
        logAction('auth.login', 'User', (int) $user['id'], [
            'email' => $user['email'],
            'role'  => $user['role'] ?? 'student',
        ], (int) $user['id']);

        // ── 8. Redirect based on role ────────────────────────────────────────
        $role = strtolower($user['role'] ?? 'student');

        if ($role === 'admin') {
            $this->redirect(APP_URL . '/dashboard');
        } elseif ($role === 'teacher') {
            $this->redirect(APP_URL . '/dashboard');
        } else {
            // student
            $this->redirect(APP_URL . '/dashboard');
        }
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    /**
     * GET /logout
     *
     * Destroy the session and redirect to the login page.
     */
    public function logout(): void
    {
        // Log before destroying the session so we still have user_id
        $user = Session::user();
        if ($user) {
            logAction('auth.logout', 'User', (int) $user['id'], [
                'email' => $user['email'] ?? null,
            ], (int) $user['id']);
        }

        Session::logout();
        Session::flash('success', 'You have been logged out successfully.');
        $this->redirect(APP_URL . '/login');
    }
}
