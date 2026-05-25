<?php

/**
 * Evidence Controller
 *
 * Handles evidence file uploads attached to violations.
 *
 * Authorization matrix:
 *   upload → admin, teacher  (students cannot submit evidence)
 *   show   → admin, teacher  (viewing raw evidence files is privileged)
 *   delete → admin only
 */

class EvidenceController extends Controller
{
    /**
     * POST /violations/{id}/evidence
     *
     * Upload and attach an evidence file to a violation.
     */
    public function upload(int $violationId): void
    {
        authorize(['admin', 'teacher']);

        // TODO (Phase 7+): validate file, move to storage/, persist record
        $this->redirect(APP_URL . '/violations/' . $violationId);
    }

    /**
     * GET /evidence/{id}
     *
     * View / download a specific evidence record.
     */
    public function show(int $id): void
    {
        authorize(['admin', 'teacher']);

        // TODO (Phase 7+): load evidence record and serve the file
        $this->view('evidence.show', [
            'title'      => 'Evidence — ' . APP_NAME,
            'pageTitle'  => 'Evidence',
            'user'       => currentUser(),
            'evidenceId' => $id,
        ]);
    }

    /**
     * POST /evidence/{id}/delete
     *
     * Remove an evidence file and its database record.
     */
    public function delete(int $id): void
    {
        authorize('admin');

        // TODO (Phase 7+): delete file from storage and remove DB record
        $this->redirect(APP_URL . '/violations');
    }
}
