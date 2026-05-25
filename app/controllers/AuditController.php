<?php

/**
 * Audit Controller
 *
 * Displays system audit logs. Restricted to Admin only.
 *
 * Audit logs contain sensitive operational records and must never be
 * accessible by teachers or students.
 */

class AuditController extends Controller
{
    /**
     * GET /admin/audit-logs
     */
    public function index(): void
    {
        authorize('admin');

        // TODO (Phase 8+): query AuditLog model and paginate results
        $this->view('audit.index', [
            'title'     => 'Audit Logs — ' . APP_NAME,
            'pageTitle' => 'Audit Logs',
            'user'      => currentUser(),
        ]);
    }
}
