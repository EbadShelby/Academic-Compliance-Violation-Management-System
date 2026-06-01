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

    // =========================================================================
    // GET /admin/audit-logs/export
    // =========================================================================

    /**
     * Export filtered audit logs to CSV.
     */
    public function export(): void
    {
        authorize('admin');

        $filters = [
            'search'    => trim($_GET['search']    ?? ''),
            'action'    => trim($_GET['action']    ?? ''),
            'user_id'   => (int) ($_GET['user_id'] ?? 0) ?: null,
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to'   => trim($_GET['date_to']   ?? ''),
        ];

        $filters = array_filter($filters, fn($v) => $v !== '' && $v !== null && $v !== 0);

        $am = $this->auditModel();
        // Fetch up to 100,000 rows to prevent memory exhaustion, effectively getting all filtered logs.
        $result = $am->getLogs($filters, 1, 100000);
        $logs = $result['rows'];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=audit_logs_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Log ID', 'Action', 'Actor Name', 'Actor Email', 'Target Type', 'Target ID', 'IP Address', 'Created At'], ',', '"', '\\');

        foreach ($logs as $row) {
            fputcsv($output, [
                $row['id'],
                $row['action'],
                $row['actor_name'] ?? 'System',
                $row['actor_email'] ?? 'N/A',
                $row['target_type'] ?? 'N/A',
                $row['target_id'] ?? 'N/A',
                $row['ip_address'] ?? 'N/A',
                $row['created_at'],
            ], ',', '"', '\\');
        }

        fclose($output);
        exit;
    }
}

