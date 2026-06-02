<?php

/**
 * Evidence Controller
 *
 * Handles evidence file uploads attached to violations.
 * All upload logic delegates to app/helpers/upload.php for security.
 *
 * Authorization matrix:
 *   upload → admin, teacher
 *   show   → admin, teacher  (serve / download a file)
 *   delete → admin only
 *
 * Routes (registered in routes/web.php):
 *   POST /violations/{id}/evidence          → upload(int $violationId)
 *   GET  /evidence/{id}                     → show(int $id)
 *   POST /evidence/{id}/delete              → delete(int $id)
 */

class EvidenceController extends Controller
{
    // ── Model factories ──────────────────────────────────────────────────────

    private function evidenceModel(): EvidenceFile
    {
        /** @var EvidenceFile */
        return $this->model('EvidenceFile');
    }

    private function violationModel(): Violation
    {
        /** @var Violation */
        return $this->model('Violation');
    }

    private function auditModel(): AuditLog
    {
        /** @var AuditLog */
        return $this->model('AuditLog');
    }

    // =========================================================================
    // POST /violations/{id}/evidence
    // =========================================================================

    /**
     * Validate and store one or more evidence files for a violation.
     *
     * Accepts:  $_FILES['evidence'] — single or multiple (name="evidence[]")
     * Redirects back to the violation show page in all cases.
     */
    public function upload(int $violationId): void
    {
        authorize(['admin', 'teacher', 'registrar']);

        $authUser  = Session::user();

        // ── 1. Ensure the violation exists ───────────────────────────────────
        $violation = $this->violationModel()->find($violationId);

        if (!$violation) {
            $this->abort(404, 'Violation not found.');
        }

        // ── 2. Normalise $_FILES array ───────────────────────────────────────
        if (empty($_FILES['evidence'])) {
            Session::flash('error', 'No file was submitted.');
            $this->redirect(APP_URL . '/violations/' . $violationId);
        }

        $files = upload_normalise_files($_FILES['evidence']);

        if (empty($files)) {
            Session::flash('error', 'No file was selected. Please choose at least one file.');
            $this->redirect(APP_URL . '/violations/' . $violationId);
        }

        // ── 3. Validate every file ───────────────────────────────────────────
        $allErrors = [];

        foreach ($files as $i => $file) {
            $fileErrors = $this->validateFile($file);
            foreach ($fileErrors as $msg) {
                $allErrors[] = $msg;
            }
        }

        if (!empty($allErrors)) {
            Session::flash('errors', $allErrors);
            $this->redirect(APP_URL . '/violations/' . $violationId);
        }

        // ── 4. Move files and persist records ────────────────────────────────
        $successCount = 0;
        $failCount    = 0;
        $em           = $this->evidenceModel();

        foreach ($files as $file) {
            $uniqueName = '';
            $absPath    = upload_move_file($file, $uniqueName);

            if ($absPath === false) {
                $failCount++;
                error_log('ACVMS EvidenceController::upload — move failed for ' . $file['name']);
                continue;
            }

            $relativePath = upload_relative_path($uniqueName);
            $realMime     = mime_content_type($absPath) ?: ($file['type'] ?? '');

            $em->saveEvidence([
                'violation_id' => $violationId,
                'uploaded_by'  => (int) $authUser['id'],
                'file_name'    => basename($file['name']),   // display name only
                'file_path'    => $relativePath,
                'mime_type'    => $realMime,
                'file_size'    => (int) ($file['size'] ?? 0),
            ]);

            // ── 5. Audit log per file ─────────────────────────────────────────
            $this->auditModel()->createLog([
                'user_id'     => $authUser['id'],
                'action'      => 'evidence.uploaded',
                'target_type' => 'Violation',
                'target_id'   => $violationId,
                'detail'      => [
                    'original_name' => basename($file['name']),
                    'stored_as'     => $uniqueName,
                    'mime_type'     => $realMime,
                    'file_size'     => $file['size'] ?? 0,
                ],
                'ip_address'  => $_SERVER['REMOTE_ADDR']     ?? null,
                'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);

            $successCount++;
        }

        // ── 6. Feedback ───────────────────────────────────────────────────────
        if ($successCount > 0 && $failCount === 0) {
            $plural = $successCount === 1 ? 'file' : 'files';
            Session::flash('success', $successCount . ' evidence ' . $plural . ' uploaded successfully.');
        } elseif ($successCount > 0 && $failCount > 0) {
            Session::flash('info', $successCount . ' file(s) uploaded; ' . $failCount . ' could not be saved.');
        } else {
            Session::flash('error', 'All files failed to upload. Please try again.');
        }

        $this->redirect(APP_URL . '/violations/' . $violationId);
    }

    // =========================================================================
    // GET /evidence/{id}
    // =========================================================================

    /**
     * Serve / download an evidence file through the application layer.
     *
     * Files are stored outside the web root's direct-serving area and
     * protected by .htaccess. This method is the only way to access them.
     */
    public function show(int $id): void
    {
        authorize(['admin', 'teacher', 'registrar']);

        $em     = $this->evidenceModel();
        $record = $em->find($id);

        if (!$record) {
            $this->abort(404, 'Evidence file not found.');
        }

        $absPath = BASE_PATH . '/' . $record['file_path'];

        if (!file_exists($absPath)) {
            $this->abort(404, 'The physical file could not be found on the server.');
        }

        // Stream the file with appropriate headers
        $mime     = $record['mime_type'] ?: mime_content_type($absPath) ?: 'application/octet-stream';
        $safeName = basename($record['file_name']);

        header('Content-Type: '        . $mime);
        header('Content-Length: '      . filesize($absPath));
        header('Content-Disposition: inline; filename="' . addslashes($safeName) . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');

        readfile($absPath);
        exit;
    }

    // =========================================================================
    // POST /evidence/{id}/delete
    // =========================================================================

    /**
     * Remove an evidence file record and its physical file.
     * Soft-delete is not available for evidence — full hard-delete only,
     * restricted to admins.
     */
    public function delete(int $id): void
    {
        authorize(['admin', 'registrar']);

        $em     = $this->evidenceModel();
        $record = $em->find($id);

        if (!$record) {
            $this->abort(404, 'Evidence file not found.');
        }

        $violationId = (int) $record['violation_id'];
        $absPath     = BASE_PATH . '/' . $record['file_path'];

        // Delete the physical file first
        if (file_exists($absPath)) {
            unlink($absPath);
        }

        // Delete the database record
        $em->delete($id);

        // Audit log
        $authUser = Session::user();
        $this->auditModel()->createLog([
            'user_id'     => $authUser['id'],
            'action'      => 'evidence.deleted',
            'target_type' => 'Violation',
            'target_id'   => $violationId,
            'detail'      => [
                'evidence_id'   => $id,
                'original_name' => $record['file_name'],
            ],
            'ip_address'  => $_SERVER['REMOTE_ADDR']     ?? null,
            'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        Session::flash('success', 'Evidence file deleted successfully.');
        $this->redirect(APP_URL . '/violations/' . $violationId);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Validate a single uploaded file using the shared upload helper.
     *
     * This is a thin wrapper that delegates to upload_validate_file()
     * so the controller has a clean, named method per the Phase 9 spec.
     *
     * @param  array    $file   Normalised single-file array.
     * @return string[]         Array of error messages (empty = valid).
     */
    private function validateFile(array $file): array
    {
        return upload_validate_file($file);
    }
}
