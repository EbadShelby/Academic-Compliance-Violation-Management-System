<?php

/**
 * Audit Controller
 *
 * Admin-only. Displays the centralized audit log viewer with
 * filtering by search term, action prefix, date range, and user.
 *
 * Routes:
 *   GET  /admin/audit-logs   → index()
 */

class AuditController extends Controller
{
    // ── Model factory ──────────────────────────────────────────────────────────

    private function auditModel(): AuditLog
    {
        /** @var AuditLog */
        return $this->model('AuditLog');
    }

    private function userModel(): User
    {
        /** @var User */
        return $this->model('User');
    }

    // =========================================================================
    // GET /admin/audit-logs
    // =========================================================================

    /**
     * Display the filterable, paginated audit log viewer.
     */
    public function index(): void
    {
        authorize('admin');

        // ── 1. Collect and sanitise filter inputs ────────────────────────────
        $filters = [
            'search'    => trim($_GET['search']    ?? ''),
            'action'    => trim($_GET['action']    ?? ''),
            'user_id'   => (int) ($_GET['user_id'] ?? 0) ?: null,
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to'   => trim($_GET['date_to']   ?? ''),
        ];

        // Remove empty / falsy values so buildWhere() skips them cleanly
        $filters = array_filter($filters, fn($v) => $v !== '' && $v !== null && $v !== 0);

        $page = max(1, (int) ($_GET['page'] ?? 1));

        // ── 2. Fetch paginated results ───────────────────────────────────────
        $am     = $this->auditModel();
        $result = $am->getLogs($filters, $page);

        // ── 3. User list for filter dropdown ────────────────────────────────
        $users = $this->userModel()->allWithRoles();

        // ── 4. Render ────────────────────────────────────────────────────────
        $this->view('audit.index', [
            'title'     => 'Audit Logs — ' . APP_NAME,
            'pageTitle' => 'Audit Logs',
            'logs'      => $result['rows'],
            'total'     => $result['total'],
            'pages'     => $result['pages'],
            'page'      => $result['page'],
            'filters'   => $filters,
            'users'     => $users,
        ]);
    }
}
