<?php

/**
 * Violation Controller
 *
 * Handles violation reporting (teachers/admins) and full
 * case management lifecycle (admins only).
 *
 * Routes:
 *   GET  /violations                    → index()
 *   GET  /violations/create             → create()
 *   POST /violations                    → store()
 *   GET  /violations/{id}               → show()
 *   GET  /violations/{id}/review        → review()        [admin]
 *   POST /violations/{id}/status        → updateStatus()  [admin]
 *   POST /violations/{id}/reject        → reject()        [admin]
 *   POST /violations/{id}/close         → close()         [admin]
 *   POST /violations/{id}/sanction      → assignSanction()[admin]
 *   GET  /violations/{id}/edit          → edit()          (stub)
 *   POST /violations/{id}               → update()        (stub)
 *   POST /violations/{id}/delete        → delete()        (stub)
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

    private function actionModel(): ViolationAction
    {
        /** @var ViolationAction */
        return $this->model('ViolationAction');
    }

    private function notificationService(): NotificationService
    {
        if (!class_exists('NotificationService', false)) {
            require_once BASE_PATH . '/app/services/NotificationService.php';
        }
        return new NotificationService();
    }

    // =========================================================================
    // GET /violations
    // =========================================================================

    /**
     * List all violations.
     * Teachers see all; students see only their own.
     */
    public function index(): void
    {
        authorize(['admin', 'teacher', 'student']);

        $vm       = $this->violationModel();
        $authUser = Session::user();
        
        $page = max(1, (int) ($_GET['page'] ?? 1));

        if ($authUser['role'] === 'student') {
            $result = $vm->getPaginatedByStudent((int) $authUser['id'], $page);
        } else {
            $result = $vm->getPaginatedWithDetails($page);
        }

        $this->view('violations.index', [
            'title'      => 'Violations — ' . APP_NAME,
            'pageTitle'  => 'Violations',
            'violations' => $result['rows'],
            'total'      => $result['total'],
            'pages'      => $result['pages'],
            'page'       => $result['page'],
        ]);
    }

    // =========================================================================
    // GET /violations/create
    // =========================================================================

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

    public function store(): void
    {
        authorize(['teacher', 'admin']);

        $authUser = Session::user();
        $data     = $this->collectInput();
        $errors   = $this->validateInput($data);

        $uploadedFiles = $this->collectUploadedFiles();
        $fileErrors    = $this->validateFiles($uploadedFiles);
        $errors        = array_merge($errors, $fileErrors);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/violations/create');
        }

        $vm          = $this->violationModel();
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

        if (!empty($uploadedFiles)) {
            $this->processUploads($uploadedFiles, $violationId, (int) $authUser['id']);
        }

        // Audit log
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
            'ip_address'  => $_SERVER['REMOTE_ADDR']     ?? null,
            'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        // Violation action record
        $this->actionModel()->createAction([
            'violation_id' => $violationId,
            'actor_id'     => $authUser['id'],
            'action_type'  => 'case_filed',
            'note'         => 'Violation report filed. Status set to Pending.',
        ]);

        // Notifications
        $ns = $this->notificationService();
        $ns->notifyStudentViolationFiled((int) $data['student_id'], $violationId);
        $ns->notifyAdminsNewViolation($violationId, $authUser['name'] ?? 'A teacher');

        Session::flash('success', 'Violation report submitted successfully.');
        $this->redirect(APP_URL . '/violations/' . $violationId);
    }

    // =========================================================================
    // GET /violations/{id}
    // =========================================================================

    public function show(int $id): void
    {
        authorize(['admin', 'teacher', 'student']);

        $violation = $this->violationModel()->findWithDetails($id);

        if (!$violation) {
            $this->abort(404, 'Violation not found.');
        }

        $authUser = Session::user();

        if ($authUser['role'] === 'student' && $violation['student_id'] != $authUser['id']) {
            $this->abort(403, 'You are not authorised to view this record.');
        }

        $evidenceFiles = $this->evidenceModel()->findByViolation($id);
        $actions       = $this->actionModel()->findByViolation($id);

        $this->view('violations.show', [
            'title'         => 'Violation #' . $id . ' — ' . APP_NAME,
            'pageTitle'     => 'Violation Report',
            'violation'     => $violation,
            'evidenceFiles' => $evidenceFiles,
            'actions'       => $actions,
        ]);
    }

    // =========================================================================
    // GET /violations/{id}/review   [admin only]
    // =========================================================================

    /**
     * Full case review page for admins.
     * Shows all case details, evidence, action history, and workflow controls.
     */
    public function review(int $id): void
    {
        authorize(['admin']);

        $vm        = $this->violationModel();
        $violation = $vm->findWithDetails($id);

        if (!$violation) {
            $this->abort(404, 'Violation not found.');
        }

        $evidenceFiles       = $this->evidenceModel()->findByViolation($id);
        $actions             = $this->actionModel()->findByViolation($id);
        $availableTransitions = $vm->getAvailableTransitions($violation['status']);
        $statusLabels        = $vm->getStatusLabels();

        $this->view('violations.review', [
            'title'                => 'Review Case #' . $id . ' — ' . APP_NAME,
            'pageTitle'            => 'Case Review',
            'violation'            => $violation,
            'evidenceFiles'        => $evidenceFiles,
            'actions'              => $actions,
            'availableTransitions' => $availableTransitions,
            'statusLabels'         => $statusLabels,
            'errors'               => Session::getFlash('errors') ?? [],
            'success'              => Session::getFlash('success') ?? null,
            'error'                => Session::getFlash('error')   ?? null,
        ]);
    }

    // =========================================================================
    // POST /violations/{id}/status  [admin only]
    // =========================================================================

    /**
     * Move a violation through the workflow state machine.
     * Blocks invalid transitions server-side.
     */
    public function updateStatus(int $id): void
    {
        authorize(['admin']);

        $vm        = $this->violationModel();
        $violation = $vm->findWithDetails($id);

        if (!$violation) {
            $this->abort(404, 'Violation not found.');
        }

        $newStatus = trim($_POST['new_status'] ?? '');
        $authUser  = Session::user();

        // Server-side transition validation
        if (!$vm->isValidTransition($violation['status'], $newStatus)) {
            Session::flash('error', sprintf(
                'Invalid status transition: cannot move from "%s" to "%s".',
                ucfirst(str_replace('_', ' ', $violation['status'])),
                ucfirst(str_replace('_', ' ', $newStatus))
            ));
            $this->redirect(APP_URL . '/violations/' . $id . '/review');
        }

        // Guard: rejection requires going through the reject() action
        if ($newStatus === 'rejected') {
            Session::flash('error', 'Use the "Reject Case" form to reject a violation (reason is required).');
            $this->redirect(APP_URL . '/violations/' . $id . '/review');
        }

        $oldStatus = $violation['status'];
        $vm->updateStatus($id, $newStatus);

        $statusLabels = $vm->getStatusLabels();
        $note = sprintf(
            'Status changed from "%s" to "%s".',
            $statusLabels[$oldStatus] ?? $oldStatus,
            $statusLabels[$newStatus] ?? $newStatus
        );

        // Violation action history
        $this->actionModel()->createAction([
            'violation_id' => $id,
            'actor_id'     => $authUser['id'],
            'action_type'  => 'status_changed',
            'note'         => $note,
        ]);

        // Audit log
        $this->auditModel()->createLog([
            'user_id'     => $authUser['id'],
            'action'      => 'violation.status_changed',
            'target_type' => 'Violation',
            'target_id'   => $id,
            'detail'      => ['from' => $oldStatus, 'to' => $newStatus],
            'ip_address'  => $_SERVER['REMOTE_ADDR']     ?? null,
            'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        // Notifications
        $ns           = $this->notificationService();
        $statusLabel  = $statusLabels[$newStatus] ?? $newStatus;
        $teacherId    = (int) ($violation['reported_by'] ?? 0);
        $studentId    = (int) ($violation['student_id']  ?? 0);

        if ($teacherId && $teacherId !== (int) $authUser['id']) {
            $ns->notifyTeacherCaseReviewed($teacherId, $id, $statusLabel);
        }
        if ($studentId) {
            if ($newStatus === 'resolved') {
                $ns->notifyStudentCaseResolved($studentId, $id);
            } else {
                $ns->notifyStudentStatusUpdated($studentId, $id, $statusLabel);
            }
        }

        Session::flash('success', 'Case status updated to "' . ($statusLabels[$newStatus] ?? $newStatus) . '".');
        $this->redirect(APP_URL . '/violations/' . $id . '/review');
    }

    // =========================================================================
    // POST /violations/{id}/reject  [admin only]
    // =========================================================================

    /**
     * Reject a violation case. Requires a non-empty rejection reason.
     * Only valid from 'under_review' status.
     */
    public function reject(int $id): void
    {
        authorize(['admin']);

        $vm        = $this->violationModel();
        $violation = $vm->findWithDetails($id);

        if (!$violation) {
            $this->abort(404, 'Violation not found.');
        }

        $reason   = trim($_POST['rejection_reason'] ?? '');
        $authUser = Session::user();

        // Rejection reason is mandatory
        if ($reason === '') {
            Session::flash('error', 'A rejection reason is required.');
            $this->redirect(APP_URL . '/violations/' . $id . '/review');
        }

        // Must be in 'under_review' to reject
        if (!$vm->isValidTransition($violation['status'], 'rejected')) {
            Session::flash('error', sprintf(
                'Cannot reject a case that is currently "%s". Move it to "Under Review" first.',
                ucfirst(str_replace('_', ' ', $violation['status']))
            ));
            $this->redirect(APP_URL . '/violations/' . $id . '/review');
        }

        $oldStatus = $violation['status'];

        $vm->updateStatus($id, 'rejected');
        $vm->addRejectionReason($id, $reason);

        $note = 'Case rejected. Reason: ' . $reason;

        $this->actionModel()->createAction([
            'violation_id' => $id,
            'actor_id'     => $authUser['id'],
            'action_type'  => 'case_rejected',
            'note'         => $note,
        ]);

        $this->auditModel()->createLog([
            'user_id'     => $authUser['id'],
            'action'      => 'violation.rejected',
            'target_type' => 'Violation',
            'target_id'   => $id,
            'detail'      => ['from' => $oldStatus, 'to' => 'rejected', 'reason' => $reason],
            'ip_address'  => $_SERVER['REMOTE_ADDR']     ?? null,
            'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        // Notifications
        $ns        = $this->notificationService();
        $teacherId = (int) ($violation['reported_by'] ?? 0);
        $studentId = (int) ($violation['student_id']  ?? 0);

        if ($teacherId && $teacherId !== (int) $authUser['id']) {
            $ns->notifyTeacherCaseRejected($teacherId, $id, $reason);
        }
        if ($studentId) {
            $ns->notifyStudentCaseRejected($studentId, $id);
        }

        Session::flash('success', 'Case has been rejected.');
        $this->redirect(APP_URL . '/violations/' . $id . '/review');
    }

    // =========================================================================
    // POST /violations/{id}/close  [admin only]
    // =========================================================================

    /**
     * Close a violation case.
     * Only valid when status is 'resolved' or 'rejected'.
     */
    public function close(int $id): void
    {
        authorize(['admin']);

        $vm        = $this->violationModel();
        $violation = $vm->findWithDetails($id);

        if (!$violation) {
            $this->abort(404, 'Violation not found.');
        }

        $authUser = Session::user();

        if (!$vm->isValidTransition($violation['status'], 'closed')) {
            Session::flash('error', sprintf(
                'Cannot close a case that is currently "%s". Only resolved or rejected cases can be closed.',
                ucfirst(str_replace('_', ' ', $violation['status']))
            ));
            $this->redirect(APP_URL . '/violations/' . $id . '/review');
        }

        $oldStatus = $violation['status'];
        $vm->updateStatus($id, 'closed');

        $note = sprintf(
            'Case closed (was "%s"). No further changes are permitted.',
            ucfirst(str_replace('_', ' ', $oldStatus))
        );

        $this->actionModel()->createAction([
            'violation_id' => $id,
            'actor_id'     => $authUser['id'],
            'action_type'  => 'case_closed',
            'note'         => $note,
        ]);

        $this->auditModel()->createLog([
            'user_id'     => $authUser['id'],
            'action'      => 'violation.closed',
            'target_type' => 'Violation',
            'target_id'   => $id,
            'detail'      => ['from' => $oldStatus, 'to' => 'closed'],
            'ip_address'  => $_SERVER['REMOTE_ADDR']     ?? null,
            'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        // Notifications
        $ns        = $this->notificationService();
        $teacherId = (int) ($violation['reported_by'] ?? 0);
        $studentId = (int) ($violation['student_id']  ?? 0);

        if ($teacherId && $teacherId !== (int) $authUser['id']) {
            $ns->notifyTeacherCaseClosed($teacherId, $id);
        }
        if ($studentId) {
            $ns->notifyStudentCaseClosed($studentId, $id);
        }

        Session::flash('success', 'Case has been closed. It is now read-only.');
        $this->redirect(APP_URL . '/violations/' . $id . '/review');
    }

    // =========================================================================
    // POST /violations/{id}/sanction  [admin only]
    // =========================================================================

    /**
     * Assign or update sanction notes on a violation.
     * Can be done at any non-closed status.
     */
    public function assignSanction(int $id): void
    {
        authorize(['admin']);

        $vm        = $this->violationModel();
        $violation = $vm->findWithDetails($id);

        if (!$violation) {
            $this->abort(404, 'Violation not found.');
        }

        if ($violation['status'] === 'closed') {
            Session::flash('error', 'Cannot modify a closed case.');
            $this->redirect(APP_URL . '/violations/' . $id . '/review');
        }

        $notes    = trim($_POST['sanction_notes'] ?? '');
        $authUser = Session::user();

        if ($notes === '') {
            Session::flash('error', 'Sanction notes cannot be empty.');
            $this->redirect(APP_URL . '/violations/' . $id . '/review');
        }

        $vm->assignSanction($id, $notes);

        $this->actionModel()->createAction([
            'violation_id' => $id,
            'actor_id'     => $authUser['id'],
            'action_type'  => 'sanction_assigned',
            'note'         => 'Sanction assigned: ' . mb_substr($notes, 0, 120) . (mb_strlen($notes) > 120 ? '…' : ''),
        ]);

        $this->auditModel()->createLog([
            'user_id'     => $authUser['id'],
            'action'      => 'violation.sanction_assigned',
            'target_type' => 'Violation',
            'target_id'   => $id,
            'detail'      => ['sanction_notes' => $notes],
            'ip_address'  => $_SERVER['REMOTE_ADDR']     ?? null,
            'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        // Notifications
        $ns        = $this->notificationService();
        $teacherId = (int) ($violation['reported_by'] ?? 0);

        if ($teacherId && $teacherId !== (int) $authUser['id']) {
            $ns->notifyTeacherSanctionAssigned($teacherId, $id);
        }

        Session::flash('success', 'Sanction notes have been saved.');
        $this->redirect(APP_URL . '/violations/' . $id . '/review');
    }

    // =========================================================================
    // GET /violations/{id}/edit
    // =========================================================================

    public function edit(int $id): void
    {
        authorize(['admin', 'teacher']);

        $violation = $this->violationModel()->findWithDetails($id);

        if (!$violation) {
            $this->abort(404, 'Violation not found.');
        }

        $authUser = Session::user();

        // Teachers can only edit their own pending cases.
        if ($authUser['role'] === 'teacher') {
            if ($violation['reported_by'] != $authUser['id']) {
                $this->abort(403, 'You can only edit violations that you reported.');
            }
            if ($violation['status'] !== 'pending') {
                Session::flash('error', 'You can only edit pending cases. This case is currently ' . str_replace('_', ' ', $violation['status']) . '.');
                $this->redirect(APP_URL . '/violations/' . $id);
            }
        }

        // Admins can edit any case unless it is closed.
        if ($authUser['role'] === 'admin') {
            if ($violation['status'] === 'closed') {
                Session::flash('error', 'Closed cases cannot be edited.');
                $this->redirect(APP_URL . '/violations/' . $id);
            }
        }

        $vm = $this->violationModel();

        $students = $this->userModel()->allStudents();
        $old      = Session::getFlash('old') ?? $violation;

        $this->view('violations.edit', [
            'title'      => 'Edit Violation #' . $id . ' — ' . APP_NAME,
            'pageTitle'  => 'Edit Violation',
            'violation'  => $violation,
            'students'   => $students,
            'categories' => $vm->getCategories(),
            'severities' => $vm->getSeverityLevels(),
            'old'        => $old,
            'errors'     => Session::getFlash('errors') ?? [],
        ]);
    }

    // =========================================================================
    // POST /violations/{id}
    // =========================================================================

    public function update(int $id): void
    {
        authorize(['admin', 'teacher']);

        $vm = $this->violationModel();
        $violation = $vm->findWithDetails($id);

        if (!$violation) {
            $this->abort(404, 'Violation not found.');
        }

        $authUser = Session::user();

        // Teachers can only edit their own pending cases.
        if ($authUser['role'] === 'teacher') {
            if ($violation['reported_by'] != $authUser['id']) {
                $this->abort(403, 'You can only edit violations that you reported.');
            }
            if ($violation['status'] !== 'pending') {
                Session::flash('error', 'You can only edit pending cases.');
                $this->redirect(APP_URL . '/violations/' . $id);
            }
        }

        // Admins can edit any case unless it is closed.
        if ($authUser['role'] === 'admin') {
            if ($violation['status'] === 'closed') {
                Session::flash('error', 'Closed cases cannot be edited.');
                $this->redirect(APP_URL . '/violations/' . $id);
            }
        }

        $data     = $this->collectInput();
        $errors   = $this->validateInput($data);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/violations/' . $id . '/edit');
        }

        $updated = $vm->updateViolation($id, [
            'student_id'    => $data['student_id'],
            'type'          => $data['type'],
            'description'   => $data['description'],
            'severity'      => $data['severity'],
            'incident_date' => $data['incident_date'],
        ]);

        if (!$updated) {
            Session::flash('error', 'Failed to update the violation report. No changes made or a database error occurred.');
            $this->redirect(APP_URL . '/violations/' . $id . '/edit');
        }

        // Audit log
        $this->auditModel()->createLog([
            'user_id'     => $authUser['id'],
            'action'      => 'violation.updated',
            'target_type' => 'Violation',
            'target_id'   => $id,
            'detail'      => [
                'type'       => $data['type'],
                'severity'   => $data['severity'],
                'student_id' => $data['student_id'],
            ],
            'ip_address'  => $_SERVER['REMOTE_ADDR']     ?? null,
            'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        // Violation action record
        $this->actionModel()->createAction([
            'violation_id' => $id,
            'actor_id'     => $authUser['id'],
            'action_type'  => 'case_updated',
            'note'         => 'Violation details were updated.',
        ]);

        Session::flash('success', 'Violation report updated successfully.');
        $this->redirect(APP_URL . '/violations/' . $id);
    }

    // =========================================================================
    // POST /violations/{id}/delete
    // =========================================================================

    public function delete(int $id): void
    {
        authorize(['admin']);

        $vm = $this->violationModel();
        $violation = $vm->findWithDetails($id);

        if (!$violation) {
            $this->abort(404, 'Violation not found.');
        }

        $evidenceModel = $this->evidenceModel();
        $evidenceFiles = $evidenceModel->findByViolation($id);

        // Delete evidence files from disk and DB
        foreach ($evidenceFiles as $file) {
            $absPath = BASE_PATH . '/public/' . ltrim($file['file_path'], '/');
            if (file_exists($absPath)) {
                @unlink($absPath);
            }
            $evidenceModel->deleteEvidence($file['id']);
        }

        // Action history and audit logs might be orphaned, but we'll let them stay for history, or if DB cascades, it's fine.
        // Usually, we should keep the audit log that it was deleted.

        $deleted = $vm->deleteViolation($id);

        if ($deleted) {
            $authUser = Session::user();
            $this->auditModel()->createLog([
                'user_id'     => $authUser['id'],
                'action'      => 'violation.deleted',
                'target_type' => 'Violation',
                'target_id'   => $id,
                'detail'      => ['deleted_violation_id' => $id],
                'ip_address'  => $_SERVER['REMOTE_ADDR']     ?? null,
                'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);

            Session::flash('success', 'Violation report deleted successfully.');
        } else {
            Session::flash('error', 'Failed to delete the violation report.');
        }

        $this->redirect(APP_URL . '/violations');
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

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

    private function collectUploadedFiles(): array
    {
        if (empty($_FILES['evidence'])) {
            return [];
        }
        return upload_normalise_files($_FILES['evidence']);
    }

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

    private function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        [$y, $m, $d] = explode('-', $date);
        return checkdate((int) $m, (int) $d, (int) $y);
    }
}
