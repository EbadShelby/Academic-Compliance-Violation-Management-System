<?php

/**
 * Violation Controller
 *
 * Handles violation reporting for teachers (and admins with oversight).
 *
 * Routes:
 *   GET  /violations          → index()
 *   GET  /violations/create   → create()
 *   POST /violations          → store()
 *   GET  /violations/{id}     → show()
 *   GET  /violations/{id}/edit   → edit()   (stub for future phase)
 *   POST /violations/{id}        → update() (stub for future phase)
 *   POST /violations/{id}/delete → delete() (stub for future phase)
 */

class ViolationController extends Controller
{
    // Upload rules are centrally defined in app/helpers/upload.php
    // (UPLOAD_MAX_SIZE = 5 MB, UPLOAD_ALLOWED_MIMES = jpg/png/pdf only)

    // ── Model factories ──────────────────────────────────────────────────────

    private function violationModel(): Violation
    {
        /** @var Violation */
        return $this->model('Violation');
    }

    private function userModel(): User
    {
        /** @var User */
        return $this->model('User');
    }

    private function evidenceModel(): EvidenceFile
    {
        /** @var EvidenceFile */
        return $this->model('EvidenceFile');
    }

    private function auditModel(): AuditLog
    {
        /** @var AuditLog */
        return $this->model('AuditLog');
    }

    // =========================================================================
    // GET /violations
    // =========================================================================

    /**
     * List all violations.
     * Teachers see all; students see only their own (handled in view).
     */
    public function index(): void
    {
        authorize(['admin', 'teacher', 'student']);

        $vm         = $this->violationModel();
        $authUser   = Session::user();

        if ($authUser['role'] === 'student') {
            // Students only see their own violations
            $violations = $vm->findByStudent((int) $authUser['id']);
        } else {
            $violations = $vm->allWithDetails();
        }

        $this->view('violations.index', [
            'title'      => 'Violations — ' . APP_NAME,
            'pageTitle'  => 'Violations',
            'violations' => $violations,
        ]);
    }

    // =========================================================================
    // GET /violations/create
    // =========================================================================

    /**
     * Show the violation-report form.
     */
    public function create(): void
    {
        authorize(['teacher', 'admin']);

        $vm       = $this->violationModel();
        $students = $this->userModel()->allStudents();
        $old      = Session::getFlash('old') ?? [];

        $this->view('violations.create', [
            'title'      => 'File Violation Report — ' . APP_NAME,
            'pageTitle'  => 'File Violation Report',
            'students'   => $students,
            'categories' => $vm->getCategories(),
            'severities' => $vm->getSeverityLevels(),
            'old'        => $old,
            'errors'     => Session::getFlash('errors') ?? [],
        ]);
    }

    // =========================================================================
    // POST /violations
    // =========================================================================

    /**
     * Validate, save violation, handle uploads, write audit log.
     */
    public function store(): void
    {
        authorize(['teacher', 'admin']);

        $authUser = Session::user();
        $data     = $this->collectInput();
        $errors   = $this->validateInput($data);

        // ── File validation (doesn't block if no files uploaded) ─────────────
        $uploadedFiles = $this->collectUploadedFiles();
        $fileErrors    = $this->validateFiles($uploadedFiles);
        $errors        = array_merge($errors, $fileErrors);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/violations/create');
        }

        // ── Save violation ───────────────────────────────────────────────────
        $vm = $this->violationModel();

        $violationId = $vm->createViolation([
            'student_id'    => $data['student_id'],
            'reported_by'   => $authUser['id'],
            'type'          => $data['type'],
            'description'   => $data['description'],
            'severity'      => $data['severity'],
            'incident_date' => $data['incident_date'],
        ]);

        if (!$violationId) {
            Session::flash('error', 'Failed to save the violation report. Please try again.');
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/violations/create');
        }

        // ── Handle file uploads ──────────────────────────────────────────────
        if (!empty($uploadedFiles)) {
            $this->processUploads($uploadedFiles, $violationId, (int) $authUser['id']);
        }

        // ── Audit log ────────────────────────────────────────────────────────
        $this->auditModel()->createLog([
            'user_id'     => $authUser['id'],
            'action'      => 'violation.created',
            'target_type' => 'Violation',
            'target_id'   => $violationId,
            'detail'      => [
                'type'       => $data['type'],
                'severity'   => $data['severity'],
                'student_id' => $data['student_id'],
            ],
            'ip_address'  => $_SERVER['REMOTE_ADDR']      ?? null,
            'user_agent'  => $_SERVER['HTTP_USER_AGENT']  ?? null,
        ]);

        Session::flash('success', 'Violation report submitted successfully.');
        $this->redirect(APP_URL . '/violations/' . $violationId);
    }

    // =========================================================================
    // GET /violations/{id}
    // =========================================================================

    /**
     * Show a single violation detail page.
     */
    public function show(int $id): void
    {
        authorize(['admin', 'teacher', 'student']);

        $violation = $this->violationModel()->findWithDetails($id);

        if (!$violation) {
            $this->abort(404, 'Violation not found.');
        }

        $authUser = Session::user();

        // Students may only view their own violations
        if ($authUser['role'] === 'student' && $violation['student_id'] != $authUser['id']) {
            $this->abort(403, 'You are not authorised to view this record.');
        }

        $evidenceFiles = $this->evidenceModel()->findByViolation($id);

        $this->view('violations.show', [
            'title'         => 'Violation #' . $id . ' — ' . APP_NAME,
            'pageTitle'     => 'Violation Report',
            'violation'     => $violation,
            'evidenceFiles' => $evidenceFiles,
        ]);
    }

    // =========================================================================
    // GET /violations/{id}/edit  (stub — future phase)
    // =========================================================================

    public function edit(int $id): void
    {
        authorize(['admin', 'teacher']);

        $violation = $this->violationModel()->findWithDetails($id);

        if (!$violation) {
            $this->abort(404, 'Violation not found.');
        }

        $vm = $this->violationModel();

        $this->view('violations.edit', [
            'title'      => 'Edit Violation #' . $id . ' — ' . APP_NAME,
            'pageTitle'  => 'Edit Violation',
            'violation'  => $violation,
            'categories' => $vm->getCategories(),
            'severities' => $vm->getSeverityLevels(),
            'errors'     => Session::getFlash('errors') ?? [],
        ]);
    }

    // =========================================================================
    // POST /violations/{id}  (stub — future phase)
    // =========================================================================

    public function update(int $id): void
    {
        authorize(['admin', 'teacher']);

        Session::flash('info', 'Violation editing will be available in the next phase.');
        $this->redirect(APP_URL . '/violations/' . $id);
    }

    // =========================================================================
    // POST /violations/{id}/delete  (stub — future phase)
    // =========================================================================

    public function delete(int $id): void
    {
        authorize(['admin']);

        Session::flash('info', 'Violation deletion will be available in the next phase.');
        $this->redirect(APP_URL . '/violations/' . $id);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Collect and sanitise POST fields.
     */
    private function collectInput(): array
    {
        return [
            'student_id'    => (int) ($_POST['student_id']    ?? 0),
            'type'          => trim($_POST['type']             ?? ''),
            'severity'      => trim($_POST['severity']         ?? ''),
            'description'   => trim($_POST['description']      ?? ''),
            'incident_date' => trim($_POST['incident_date']    ?? ''),
        ];
    }

    /**
     * Server-side validation for the create form.
     */
    private function validateInput(array $data): array
    {
        $errors = [];

        if ($data['student_id'] <= 0) {
            $errors['student_id'] = 'Please select a student.';
        }

        if ($data['type'] === '') {
            $errors['type'] = 'Please select a violation category.';
        }

        $validSeverities = ['minor', 'moderate', 'major', 'critical'];
        if (!in_array($data['severity'], $validSeverities, true)) {
            $errors['severity'] = 'Please select a valid severity level.';
        }

        if ($data['description'] === '') {
            $errors['description'] = 'Description is required.';
        } elseif (mb_strlen($data['description']) < 20) {
            $errors['description'] = 'Description must be at least 20 characters.';
        } elseif (mb_strlen($data['description']) > 5000) {
            $errors['description'] = 'Description may not exceed 5000 characters.';
        }

        if ($data['incident_date'] === '') {
            $errors['incident_date'] = 'Incident date is required.';
        } elseif (!$this->isValidDate($data['incident_date'])) {
            $errors['incident_date'] = 'Please enter a valid date (YYYY-MM-DD).';
        } elseif ($data['incident_date'] > date('Y-m-d')) {
            $errors['incident_date'] = 'Incident date cannot be in the future.';
        }

        return $errors;
    }

    /**
     * Collect all uploaded files from $_FILES['evidence'].
     * Delegates to the shared upload helper (app/helpers/upload.php).
     */
    private function collectUploadedFiles(): array
    {
        if (empty($_FILES['evidence'])) {
            return [];
        }
        return upload_normalise_files($_FILES['evidence']);
    }

    /**
     * Validate all uploaded files.
     * Delegates to upload_validate_file() in app/helpers/upload.php.
     * Returns a flat array of error strings.
     */
    private function validateFiles(array $files): array
    {
        $errors = [];
        foreach ($files as $i => $file) {
            $fileErrors = upload_validate_file($file);
            foreach ($fileErrors as $msg) {
                $errors['evidence_' . $i] = $msg;
            }
        }
        return $errors;
    }

    /**
     * Move uploaded files to storage/uploads/evidence/ and insert evidence records.
     * Delegates file moving and path generation to app/helpers/upload.php.
     */
    private function processUploads(array $files, int $violationId, int $uploaderId): void
    {
        $evidenceModel = $this->evidenceModel();

        foreach ($files as $file) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }

            $uniqueName = '';
            $absPath    = upload_move_file($file, $uniqueName);

            if ($absPath === false) {
                error_log('ACVMS ViolationController::processUploads — move failed for ' . $file['name']);
                continue;
            }

            $relativePath = upload_relative_path($uniqueName);
            $realMime     = mime_content_type($absPath) ?: ($file['type'] ?? '');

            $evidenceModel->saveEvidence([
                'violation_id' => $violationId,
                'uploaded_by'  => $uploaderId,
                'file_name'    => basename($file['name']),
                'file_path'    => $relativePath,
                'mime_type'    => $realMime,
                'file_size'    => (int) ($file['size'] ?? 0),
            ]);
        }
    }

    /**
     * Basic date format validation (YYYY-MM-DD).
     */
    private function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        [$y, $m, $d] = explode('-', $date);
        return checkdate((int) $m, (int) $d, (int) $y);
    }
}
