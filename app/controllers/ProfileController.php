<?php

/**
 * Profile Controller
 *
 * Handles self-service user profile updates (basic details, password).
 */

class ProfileController extends Controller
{
    private const NAME_MAX  = 100;
    private const EMAIL_MAX = 191;
    private const PASS_MIN  = 8;

    private function userModel(): User
    {
        /** @var User */
        return $this->model('User');
    }

    /**
     * Show the profile edit form.
     */
    public function index(): void
    {
        AuthMiddleware::handle();
        $authUser = Session::user();
        
        $user = $this->userModel()->findWithRole((int)$authUser['id']);
        if (!$user) {
            $this->abort(404, 'User not found.');
        }

        $this->view('profile.index', [
            'title'     => 'My Profile — ' . APP_NAME,
            'pageTitle' => 'My Profile',
            'user'      => $user,
            'errors'    => Session::getFlash('errors') ?? [],
            'old'       => Session::getFlash('old') ?? [],
        ]);
    }

    /**
     * Update basic user details.
     */
    public function update(): void
    {
        AuthMiddleware::handle();
        $authUser = Session::user();
        $userId = (int)$authUser['id'];

        $userModel = $this->userModel();
        $existing = $userModel->findWithRole($userId);
        if (!$existing) {
            $this->abort(404, 'User not found.');
        }

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $email     = strtolower(trim($_POST['email'] ?? ''));

        $errors = [];

        if ($firstName === '') {
            $errors['first_name'] = 'First name is required.';
        } elseif (mb_strlen($firstName) > self::NAME_MAX) {
            $errors['first_name'] = 'First name may not exceed ' . self::NAME_MAX . ' characters.';
        }

        if ($lastName === '') {
            $errors['last_name'] = 'Last name is required.';
        } elseif (mb_strlen($lastName) > self::NAME_MAX) {
            $errors['last_name'] = 'Last name may not exceed ' . self::NAME_MAX . ' characters.';
        }

        if ($email === '') {
            $errors['email'] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif (mb_strlen($email) > self::EMAIL_MAX) {
            $errors['email'] = 'Email address is too long.';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            $this->redirect(APP_URL . '/profile');
        }

        if ($userModel->emailExists($email, $userId)) {
            Session::flash('errors', ['email' => 'This email address is already in use by another account.']);
            Session::flash('old', $_POST);
            $this->redirect(APP_URL . '/profile');
        }

        // Prepare data for update (maintaining role_id and student_id securely)
        $updateData = [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'role_id'    => $existing['role_id'],
            'student_id' => $existing['student_id'],
            'is_active'  => $existing['is_active'],
            'password'   => ''
        ];

        $userModel->updateUser($userId, $updateData);

        // Update session data
        $_SESSION['auth_user']['name']  = $firstName . ' ' . $lastName;
        $_SESSION['auth_user']['email'] = $email;

        logAction('profile.updated', 'User', $userId);

        Session::flash('success', 'Profile updated successfully.');
        $this->redirect(APP_URL . '/profile');
    }

    /**
     * Update user password.
     */
    public function updatePassword(): void
    {
        AuthMiddleware::handle();
        $authUser = Session::user();
        $userId = (int)$authUser['id'];

        $userModel = $this->userModel();
        $existing = $userModel->findWithRole($userId);
        if (!$existing) {
            $this->abort(404, 'User not found.');
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if ($currentPassword === '') {
            $errors['current_password'] = 'Current password is required.';
        } elseif (!$userModel->verifyPassword($currentPassword, $existing['password'])) {
            $errors['current_password'] = 'Current password is incorrect.';
        }

        if ($newPassword === '') {
            $errors['new_password'] = 'New password is required.';
        } else {
            if (mb_strlen($newPassword) < self::PASS_MIN) {
                $errors['new_password'] = 'Password must be at least ' . self::PASS_MIN . ' characters.';
            } elseif (!preg_match('/[A-Z]/', $newPassword)) {
                $errors['new_password'] = 'Password must contain at least one uppercase letter.';
            } elseif (!preg_match('/[0-9]/', $newPassword)) {
                $errors['new_password'] = 'Password must contain at least one number.';
            }
        }

        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'New passwords do not match.';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            $this->redirect(APP_URL . '/profile');
        }

        $userModel->resetPassword($userId, $newPassword);
        
        logAction('profile.password_updated', 'User', $userId);

        Session::flash('success', 'Password updated successfully.');
        $this->redirect(APP_URL . '/profile');
    }
}
