<?php

/**
 * User Controller
 *
 * Admin-only module for managing all system users.
 * Every action is guarded with authorize('admin').
 *
 * Routes:
 *   GET  /admin/users              → index()
 *   GET  /admin/users/create       → create()
 *   POST /admin/users              → store()
 *   GET  /admin/users/{id}         → show()
 *   GET  /admin/users/{id}/edit    → edit()
 *   POST /admin/users/{id}         → update()
 *   POST /admin/users/{id}/delete  → delete()
 */

class UserController extends Controller
{
    // ── Shared validation rules ───────────────────────────────────────────────

    private const NAME_MAX      = 100;
    private const EMAIL_MAX     = 191;
    private const PASS_MIN      = 8;
    private const STUDENT_ID_MAX = 64;

    // ── Helper: load both models ──────────────────────────────────────────────

    private function userModel(): User
    {
        /** @var User */
        return $this->model('User');
    }

    private function roleModel(): Role
    {
        /** @var Role */
        return $this->model('Role');
    }

    // =========================================================================
    // GET /admin/users
    // =========================================================================

    /**
     * Display the paginated user list.
     */
    public function index(): void
    {
        authorize('admin');

        $users = $this->userModel()->allWithRoles();

        $this->view('users.index', [
            'title'     => 'User Management — ' . APP_NAME,
            'pageTitle' => 'User Management',
            'users'     => $users,
        ]);
    }

    // =========================================================================
    // GET /admin/users/import
    // =========================================================================

    /**
     * Show the bulk CSV import form.
     */
    public function import(): void
    {
        authorize('admin');

        $this->view('users.import', [
            'title'     => 'Import Users — ' . APP_NAME,
            'pageTitle' => 'Import Users (CSV)',
            'errors'    => Session::getFlash('errors') ?? [],
        ]);
    }

    // =========================================================================
    // POST /admin/users/import
    // =========================================================================

    /**
     * Process the uploaded CSV file.
     */
    public function processImport(): void
    {
        authorize('admin');

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Please select a valid CSV file to upload.');
            $this->redirect(APP_URL . '/admin/users/import');
        }

        $fileTmpPath = $_FILES['csv_file']['tmp_name'];
        $fileName = $_FILES['csv_file']['name'];
        
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        if ($fileExtension !== 'csv') {
            Session::flash('error', 'Invalid file extension. Only CSV files are allowed.');
            $this->redirect(APP_URL . '/admin/users/import');
        }

        if (($handle = fopen($fileTmpPath, "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ",");
            if (!$header) {
                Session::flash('error', 'The uploaded file is empty or has an invalid format.');
                $this->redirect(APP_URL . '/admin/users/import');
            }

            // Normalize header
            $header = array_map('trim', $header);
            $header = array_map('strtolower', $header);

            $requiredCols = ['first_name', 'last_name', 'email', 'role'];
            foreach ($requiredCols as $col) {
                if (!in_array($col, $header)) {
                    Session::flash('error', "Missing required column: '{$col}'. Please check the template format.");
                    $this->redirect(APP_URL . '/admin/users/import');
                }
            }

            $userModel = $this->userModel();
            $roleMap = $this->roleModel()->allSlugsToIds();

            $successCount = 0;
            $errors = [];
            $rowNum = 1; // Header is row 1

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $rowNum++;
                // Combine header with data, if column counts match
                if (count($header) !== count($data)) {
                    $errors[] = "Row {$rowNum}: Column count mismatch.";
                    continue;
                }

                $row = array_combine($header, $data);
                
                $firstName = trim($row['first_name'] ?? '');
                $lastName = trim($row['last_name'] ?? '');
                $email = strtolower(trim($row['email'] ?? ''));
                $roleSlug = strtolower(trim($row['role'] ?? ''));
                $studentId = isset($row['student_id']) ? trim($row['student_id']) : '';
                $password = isset($row['password']) && trim($row['password']) !== '' ? trim($row['password']) : 'TempPass123!';

                if (empty($firstName) || empty($lastName) || empty($email) || empty($roleSlug)) {
                    $errors[] = "Row {$rowNum}: Missing required fields.";
                    continue;
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Row {$rowNum}: Invalid email format ({$email}).";
                    continue;
                }

                if (!isset($roleMap[$roleSlug])) {
                    $errors[] = "Row {$rowNum}: Invalid role '{$roleSlug}'. Must be admin, teacher, or student.";
                    continue;
                }

                if ($userModel->emailExists($email)) {
                    $errors[] = "Row {$rowNum}: Email '{$email}' already exists.";
                    continue;
                }

                if (!empty($studentId) && $userModel->studentIdExists($studentId)) {
                    $errors[] = "Row {$rowNum}: Student ID '{$studentId}' already in use.";
                    continue;
                }

                $userData = [
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'email'      => $email,
                    'role_id'    => $roleMap[$roleSlug],
                    'student_id' => $studentId,
                    'password'   => $password
                ];

                $newId = $userModel->createUser($userData);
                if ($newId) {
                    $successCount++;
                } else {
                    $errors[] = "Row {$rowNum}: Database error creating user '{$email}'.";
                }
            }
            fclose($handle);

            if ($successCount > 0) {
                logAction('user.bulk_imported', 'User', 0, ['count' => $successCount, 'errors' => count($errors)]);
                $msg = "Successfully imported {$successCount} users.";
                if (!empty($errors)) {
                    $msg .= " However, some rows had errors and were skipped.";
                    Session::flash('errors', $errors);
                }
                Session::flash('success', $msg);
            } else {
                Session::flash('error', 'No users were imported. Please check the errors below.');
                Session::flash('errors', $errors);
                $this->redirect(APP_URL . '/admin/users/import');
            }

            $this->redirect(APP_URL . '/admin/users');
        } else {
             Session::flash('error', 'Could not read the uploaded file.');
             $this->redirect(APP_URL . '/admin/users/import');
        }
    }

    // =========================================================================
    // GET /admin/users/create
    // =========================================================================

    /**
     * Show the create-user form.
     */
    public function create(): void
    {
        authorize('admin');

        $roles = $this->roleModel()->allForSelect();
        $old   = Session::getFlash('old') ?? [];

        $this->view('users.create', [
            'title'     => 'Create User — ' . APP_NAME,
            'pageTitle' => 'Create New User',
            'roles'     => $roles,
            'old'       => $old,
            'errors'    => Session::getFlash('errors') ?? [],
        ]);
    }

    // =========================================================================
    // POST /admin/users
    // =========================================================================

    /**
     * Validate and persist a new user.
     */
    public function store(): void
    {
        authorize('admin');

        $data   = $this->collectInput();
        $errors = $this->validateCreate($data);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/admin/users/create');
        }

        $userModel = $this->userModel();

        if ($userModel->emailExists($data['email'])) {
            Session::flash('errors', ['email' => 'This email address is already registered.']);
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/admin/users/create');
        }

        if (!empty($data['student_id']) && $userModel->studentIdExists($data['student_id'])) {
            Session::flash('errors', ['student_id' => 'This Student ID is already in use.']);
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/admin/users/create');
        }

        $newId = $userModel->createUser($data);

        if (!$newId) {
            Session::flash('error', 'Failed to create user. Please try again.');
            $this->redirect(APP_URL . '/admin/users/create');
        }

        // Audit log
        logAction('user.created', 'User', $newId, [
            'email'   => $data['email'],
            'role_id' => $data['role_id'],
        ]);

        Session::flash('success', 'User created successfully.');
        $this->redirect(APP_URL . '/admin/users/' . $newId);
    }

    // =========================================================================
    // GET /admin/users/{id}
    // =========================================================================

    /**
     * Show a single user's profile.
     */
    public function show(int $id): void
    {
        authorize('admin');

        $user = $this->userModel()->findWithRole($id);

        if (!$user) {
            $this->abort(404, 'User not found.');
        }

        $this->view('users.show', [
            'title'     => htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . ' — ' . APP_NAME,
            'pageTitle' => 'User Profile',
            'user'      => $user,
        ]);
    }

    // =========================================================================
    // GET /admin/users/{id}/edit
    // =========================================================================

    /**
     * Show the edit-user form.
     */
    public function edit(int $id): void
    {
        authorize('admin');

        $user = $this->userModel()->findWithRole($id);

        if (!$user) {
            $this->abort(404, 'User not found.');
        }

        $roles = $this->roleModel()->allForSelect();
        $old   = Session::getFlash('old') ?? [];

        // Merge DB data with re-populated old input (if any)
        $merged = empty($old) ? $user : array_merge($user, $old);

        $this->view('users.edit', [
            'title'     => 'Edit User — ' . APP_NAME,
            'pageTitle' => 'Edit User',
            'user'      => $user,
            'merged'    => $merged,
            'roles'     => $roles,
            'errors'    => Session::getFlash('errors') ?? [],
        ]);
    }

    // =========================================================================
    // POST /admin/users/{id}
    // =========================================================================

    /**
     * Validate and persist changes to an existing user.
     */
    public function update(int $id): void
    {
        authorize('admin');

        $userModel = $this->userModel();
        $existing  = $userModel->findWithRole($id);

        if (!$existing) {
            $this->abort(404, 'User not found.');
        }

        $data   = $this->collectInput();
        $errors = $this->validateUpdate($data, $id);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/admin/users/' . $id . '/edit');
        }

        if ($userModel->emailExists($data['email'], $id)) {
            Session::flash('errors', ['email' => 'This email address is already in use by another account.']);
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/admin/users/' . $id . '/edit');
        }

        if (!empty($data['student_id']) && $userModel->studentIdExists($data['student_id'], $id)) {
            Session::flash('errors', ['student_id' => 'This Student ID is already in use by another account.']);
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/admin/users/' . $id . '/edit');
        }

        $userModel->updateUser($id, $data);

        // Audit log
        logAction('user.updated', 'User', $id, [
            'email'   => $data['email'],
            'role_id' => $data['role_id'],
        ]);

        Session::flash('success', 'User updated successfully.');
        $this->redirect(APP_URL . '/admin/users/' . $id);
    }

    // =========================================================================
    // POST /admin/users/{id}/delete  (soft-deactivate / reactivate / reset-pw)
    // =========================================================================

    /**
     * Handle deactivate, reactivate, and password-reset sub-actions.
     *
     * _action values: 'deactivate' | 'reactivate' | 'reset_password'
     */
    public function delete(int $id): void
    {
        authorize('admin');

        $userModel = $this->userModel();
        $user      = $userModel->find($id);

        if (!$user) {
            $this->abort(404, 'User not found.');
        }

        $action = trim($_POST['_action'] ?? '');

        switch ($action) {
            case 'deactivate':
                $userModel->setActive($id, false);
                logAction('user.deactivated', 'User', $id, ['target_email' => $user['email'] ?? null]);
                Session::flash('success', 'User account has been deactivated.');
                break;

            case 'reactivate':
                $userModel->setActive($id, true);
                logAction('user.reactivated', 'User', $id, ['target_email' => $user['email'] ?? null]);
                Session::flash('success', 'User account has been reactivated.');
                break;

            case 'reset_password':
                $newPassword = trim($_POST['new_password'] ?? '');
                $errors      = $this->validatePassword($newPassword, 'new_password');

                if (!empty($errors)) {
                    Session::flash('errors', $errors);
                    $this->redirect(APP_URL . '/admin/users/' . $id . '/edit');
                }

                $userModel->resetPassword($id, $newPassword);
                logAction('user.password_reset', 'User', $id, ['target_email' => $user['email'] ?? null]);
                Session::flash('success', 'Password has been reset successfully.');
                break;

            default:
                Session::flash('error', 'Unknown action.');
        }

        $this->redirect(APP_URL . '/admin/users/' . $id);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Collect and trim all expected POST fields.
     */
    private function collectInput(): array
    {
        return [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name'  => trim($_POST['last_name']  ?? ''),
            'email'      => strtolower(trim($_POST['email'] ?? '')),
            'role_id'    => (int) ($_POST['role_id'] ?? 0),
            'student_id' => trim($_POST['student_id'] ?? ''),
            'password'   => $_POST['password']         ?? '',
            'is_active'  => isset($_POST['is_active']) ? 1 : 0,
        ];
    }

    /**
     * Validate inputs for CREATE (password is required).
     */
    private function validateCreate(array $data): array
    {
        $errors = $this->validateCommon($data);

        if ($data['password'] === '') {
            $errors['password'] = 'Password is required.';
        } else {
            $pwErrors = $this->validatePassword($data['password'], 'password');
            $errors   = array_merge($errors, $pwErrors);
        }

        return $errors;
    }

    /**
     * Validate inputs for UPDATE (password is optional).
     */
    private function validateUpdate(array $data, int $excludeId = 0): array
    {
        $errors = $this->validateCommon($data);

        if ($data['password'] !== '') {
            $pwErrors = $this->validatePassword($data['password'], 'password');
            $errors   = array_merge($errors, $pwErrors);
        }

        return $errors;
    }

    /**
     * Shared field validation (used for both create and update).
     */
    private function validateCommon(array $data): array
    {
        $errors = [];

        if ($data['first_name'] === '') {
            $errors['first_name'] = 'First name is required.';
        } elseif (mb_strlen($data['first_name']) > self::NAME_MAX) {
            $errors['first_name'] = 'First name may not exceed ' . self::NAME_MAX . ' characters.';
        }

        if ($data['last_name'] === '') {
            $errors['last_name'] = 'Last name is required.';
        } elseif (mb_strlen($data['last_name']) > self::NAME_MAX) {
            $errors['last_name'] = 'Last name may not exceed ' . self::NAME_MAX . ' characters.';
        }

        if ($data['email'] === '') {
            $errors['email'] = 'Email address is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif (mb_strlen($data['email']) > self::EMAIL_MAX) {
            $errors['email'] = 'Email address is too long.';
        }

        if ($data['role_id'] <= 0) {
            $errors['role_id'] = 'Please select a role.';
        }

        if (!empty($data['student_id']) && mb_strlen($data['student_id']) > self::STUDENT_ID_MAX) {
            $errors['student_id'] = 'Student ID may not exceed ' . self::STUDENT_ID_MAX . ' characters.';
        }

        return $errors;
    }

    /**
     * Validate a password value for a given field key.
     */
    private function validatePassword(string $password, string $field = 'password'): array
    {
        $errors = [];

        if (mb_strlen($password) < self::PASS_MIN) {
            $errors[$field] = 'Password must be at least ' . self::PASS_MIN . ' characters.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors[$field] = 'Password must contain at least one uppercase letter.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors[$field] = 'Password must contain at least one number.';
        }

        return $errors;
    }
}
