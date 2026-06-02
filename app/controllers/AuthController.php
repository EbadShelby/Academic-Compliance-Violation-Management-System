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
        // ── 0. Rate Limiting ─────────────────────────────────────────────────
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        /** @var AuditLog $auditLogModel */
        $auditLogModel = $this->model('AuditLog');
        
        // Block if 5 or more failed attempts in the last 5 minutes
        if ($auditLogModel->countFailedLoginsByIp($ip, 5) >= 5) {
            logAction('auth.rate_limit_exceeded', null, null, [
                'ip'    => $ip,
                'email' => trim($_POST['email'] ?? '')
            ]);
            
            Session::flash('error', 'Too many failed login attempts. Please try again in 5 minutes.');
            $this->redirect(APP_URL . '/login');
        }

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
    // ── Forgot Password ───────────────────────────────────────────────────────

    /**
     * GET /forgot-password
     * Show the forgot password form.
     */
    public function showForgotPassword(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect(APP_URL . '/dashboard');
        }

        $this->view('auth.forgot-password', [
            'title'   => 'Forgot Password — ' . APP_NAME,
            'error'   => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
            'old'     => Session::getFlash('old') ?? [],
        ], '');
    }

    /**
     * POST /forgot-password
     * Handle the forgot password submission and send the reset email.
     */
    public function sendResetLink(): void
    {
        // 0. Rate Limiting for forgot password
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        /** @var AuditLog $auditLogModel */
        $auditLogModel = $this->model('AuditLog');
        
        if ($auditLogModel->countFailedLoginsByIp($ip, 15) >= 10) {
            Session::flash('error', 'Too many requests. Please try again later.');
            $this->redirect(APP_URL . '/forgot-password');
        }

        $email = trim($_POST['email'] ?? '');
        Session::flash('old', ['email' => $email]);

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please enter a valid email address.');
            $this->redirect(APP_URL . '/forgot-password');
        }

        /** @var User $userModel */
        $userModel = $this->model('User');
        $user = $userModel->findByEmail($email);

        // ALWAYS show success message to prevent user enumeration
        $successMessage = 'If that email is in our system, we have sent a password reset link.';

        if ($user && isset($user['is_active']) && (int) $user['is_active'] === 1) {
            /** @var PasswordReset $resetModel */
            $resetModel = $this->model('PasswordReset');
            
            try {
                $rawToken = $resetModel->createToken($email);
                
                // Build the reset link
                $resetLink = APP_URL . '/reset-password?token=' . urlencode($rawToken) . '&email=' . urlencode($email);
                
                $htmlBody = '
                    <p>Hello ' . htmlspecialchars($user['first_name']) . ',</p>
                    <p>You recently requested to reset your password for your ' . htmlspecialchars(APP_NAME) . ' account. Click the button below to reset it.</p>
                    <a href="' . htmlspecialchars($resetLink) . '" class="btn">Reset Password</a>
                    <p>If you did not request a password reset, please ignore this email. This link will expire in 1 hour.</p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p><a href="' . htmlspecialchars($resetLink) . '">' . htmlspecialchars($resetLink) . '</a></p>
                ';
                
                require_once BASE_PATH . '/app/services/MailerService.php';
                $mailer = new MailerService();
                $mailer->sendHtml($email, 'Password Reset Request', $htmlBody);
                
                // Include the reset link in the audit log as a fallback for testing in environments where mail() is disabled
                logAction('auth.password_reset_requested', 'User', (int)$user['id'], [
                    'email' => $email,
                    'reset_link' => $resetLink
                ]);

            } catch (Exception $e) {
                // Log error but don't show to user
                error_log('Password reset error: ' . $e->getMessage());
            }
        }

        Session::flash('success', $successMessage);
        $this->redirect(APP_URL . '/forgot-password');
    }

    // ── Reset Password ────────────────────────────────────────────────────────

    /**
     * GET /reset-password
     * Show the reset password form.
     */
    public function showResetPassword(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect(APP_URL . '/dashboard');
        }

        $token = $_GET['token'] ?? '';
        $email = $_GET['email'] ?? '';

        if (empty($token) || empty($email)) {
            Session::flash('error', 'Invalid or missing password reset token.');
            $this->redirect(APP_URL . '/login');
        }

        $this->view('auth.reset-password', [
            'title' => 'Reset Password — ' . APP_NAME,
            'error' => Session::getFlash('error'),
            'token' => $token,
            'email' => $email
        ], '');
    }

    /**
     * POST /reset-password
     * Handle the actual password reset.
     */
    public function resetPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirmation'] ?? '';

        if (empty($token) || empty($email) || empty($password) || empty($passwordConfirm)) {
            Session::flash('error', 'All fields are required.');
            $this->redirect(APP_URL . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email));
        }

        if ($password !== $passwordConfirm) {
            Session::flash('error', 'Passwords do not match.');
            $this->redirect(APP_URL . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email));
        }

        if (strlen($password) < 8) {
            Session::flash('error', 'Password must be at least 8 characters long.');
            $this->redirect(APP_URL . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email));
        }

        /** @var PasswordReset $resetModel */
        $resetModel = $this->model('PasswordReset');
        
        if (!$resetModel->isValid($email, $token)) {
            Session::flash('error', 'This password reset token is invalid or has expired.');
            $this->redirect(APP_URL . '/login');
        }

        /** @var User $userModel */
        $userModel = $this->model('User');
        $user = $userModel->findByEmail($email);

        if (!$user) {
            Session::flash('error', 'An error occurred. Please try again.');
            $this->redirect(APP_URL . '/login');
        }

        // Update password
        $userModel->resetPassword((int)$user['id'], $password);
        
        // Invalidate tokens
        $resetModel->deleteByEmail($email);
        
        logAction('auth.password_reset_completed', 'User', (int)$user['id'], ['email' => $email]);

        Session::flash('success', 'Your password has been successfully reset. You can now log in.');
        $this->redirect(APP_URL . '/login');
    }
}
