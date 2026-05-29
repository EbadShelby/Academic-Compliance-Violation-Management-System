<?php
/**
 * views/audit/index.php
 *
 * Admin-only centralized audit log viewer.
 * Features: search, action-prefix filter, date range, user filter, pagination.
 */

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Return a Bootstrap badge class for a given action key.
 */
function auditBadgeClass(string $action): string
{
    $prefix = explode('.', $action)[0] ?? '';
    return match ($prefix) {
        'auth'      => 'bg-primary',
        'user'      => 'bg-info text-dark',
        'violation' => 'bg-warning text-dark',
        'evidence'  => 'bg-secondary',
        default     => 'bg-dark',
    };
}

/**
 * Return a Bootstrap icon class for a given action key.
 */
function auditIcon(string $action): string
{
    return match (true) {
        str_starts_with($action, 'auth.login_failed')  => 'bi-shield-x',
        str_starts_with($action, 'auth.login')         => 'bi-box-arrow-in-right',
        str_starts_with($action, 'auth.logout')        => 'bi-box-arrow-right',
        str_starts_with($action, 'user.created')       => 'bi-person-plus',
        str_starts_with($action, 'user.updated')       => 'bi-person-gear',
        str_starts_with($action, 'user.deactivated')   => 'bi-person-dash',
        str_starts_with($action, 'user.reactivated')   => 'bi-person-check',
        str_starts_with($action, 'user.password')      => 'bi-key',
        str_starts_with($action, 'violation.created')  => 'bi-file-earmark-plus',
        str_starts_with($action, 'violation.status')   => 'bi-arrow-repeat',
        str_starts_with($action, 'violation.rejected') => 'bi-x-circle',
        str_starts_with($action, 'violation.closed')   => 'bi-lock',
        str_starts_with($action, 'violation.sanction') => 'bi-patch-exclamation',
        str_starts_with($action, 'evidence')           => 'bi-paperclip',
        default                                        => 'bi-activity',
    };
}

// ── Distinct action prefixes for the filter dropdown ─────────────────────────
$actionGroups = [
    ''          => 'All Actions',
    'auth'      => 'Authentication',
    'user'      => 'User Management',
    'violation' => 'Violations',
    'evidence'  => 'Evidence',
];

// ── Current filters (safe for HTML output) ───────────────────────────────────
$fSearch   = htmlspecialchars($filters['search']    ?? '');
$fAction   = htmlspecialchars($filters['action']    ?? '');
$fUserId   = (int) ($filters['user_id']             ?? 0);
$fDateFrom = htmlspecialchars($filters['date_from'] ?? '');
$fDateTo   = htmlspecialchars($filters['date_to']   ?? '');
?>

<style>
/* ── Audit log page styles ──────────────────────────────────────────────────── */
.audit-card {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,.07);
    border-radius: .875rem;
    overflow: hidden;
}
.audit-filter-bar {
    background: rgba(255,255,255,.03);
    border-bottom: 1px solid rgba(255,255,255,.07);
    padding: 1.25rem 1.5rem;
}
.audit-filter-bar .form-control,
.audit-filter-bar .form-select {
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.1);
    color: #f8fafc;
    font-size: .825rem;
}
.audit-filter-bar .form-control::placeholder { color: #64748b; }
.audit-filter-bar .form-control:focus,
.audit-filter-bar .form-select:focus {
    background: rgba(255,255,255,.08);
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79,70,229,.2);
    color: #f8fafc;
}
.audit-filter-bar .form-select option { background: #1e293b; color: #f8fafc; }
.audit-table thead th {
    background: rgba(255,255,255,.04);
    color: #94a3b8;
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    border-bottom: 1px solid rgba(255,255,255,.07);
    padding: .875rem 1rem;
    white-space: nowrap;
}
.audit-table tbody tr {
    border-bottom: 1px solid rgba(255,255,255,.05);
    transition: background .15s;
}
.audit-table tbody tr:hover { background: rgba(255,255,255,.03); }
.audit-table td { padding: .75rem 1rem; vertical-align: middle; font-size: .825rem; color: #cbd5e1; }
.audit-table td.col-action { min-width: 180px; }
.audit-table td.col-detail { max-width: 260px; font-size: .75rem; color: #64748b; }

.action-badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .04em;
    padding: .3rem .6rem;
    border-radius: 999px;
}
.actor-name { font-weight: 600; color: #e2e8f0; font-size: .825rem; }
.actor-email { font-size: .7rem; color: #64748b; }
.ip-text { font-family: monospace; font-size: .75rem; color: #94a3b8; }
.ts-text  { font-size: .75rem; color: #64748b; white-space: nowrap; }

.detail-pill {
    display: inline-block;
    background: rgba(255,255,255,.06);
    border-radius: .375rem;
    padding: .2rem .45rem;
    font-family: monospace;
    font-size: .7rem;
    color: #94a3b8;
    word-break: break-all;
    max-width: 100%;
}

/* Pagination */
.audit-pagination .page-link {
    background: rgba(255,255,255,.04);
    border-color: rgba(255,255,255,.1);
    color: #94a3b8;
    font-size: .8rem;
}
.audit-pagination .page-item.active .page-link {
    background: #4f46e5;
    border-color: #4f46e5;
    color: #fff;
}
.audit-pagination .page-item.disabled .page-link { opacity: .4; }

.stat-badge {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    background: rgba(79,70,229,.15);
    border: 1px solid rgba(79,70,229,.3);
    color: #a5b4fc;
    border-radius: .5rem;
    padding: .35rem .75rem;
    font-size: .8rem;
    font-weight: 600;
}

.empty-state {
    padding: 4rem 1.5rem;
    text-align: center;
    color: #475569;
}
.empty-state i { font-size: 2.5rem; margin-bottom: 1rem; display: block; }
</style>

<!-- ── Page Header ──────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h2 class="mb-1 fw-bold" style="font-size:1.25rem;">
            <i class="bi bi-journal-text me-2 text-primary"></i>Audit Log
        </h2>
        <p class="text-muted mb-0" style="font-size:.825rem;">
            Immutable record of all system events. Logs cannot be edited or deleted.
        </p>
    </div>
    <span class="stat-badge">
        <i class="bi bi-list-ol"></i>
        <?= number_format($total) ?> total entr<?= $total === 1 ? 'y' : 'ies' ?>
    </span>
</div>

<!-- ── Main card ───────────────────────────────────────────────────────────── -->
<div class="audit-card">

    <!-- Filter bar -->
    <form method="GET" action="<?= APP_URL ?>/admin/audit-logs" class="audit-filter-bar" id="auditFilterForm">
        <div class="row g-2 align-items-end">

            <!-- Search -->
            <div class="col-12 col-md-4">
                <label class="form-label text-muted mb-1" style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                    Search
                </label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text" style="background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.1);color:#64748b;">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text"
                           name="search"
                           id="auditSearch"
                           class="form-control"
                           placeholder="Action, user, email, IP…"
                           value="<?= $fSearch ?>"
                           autocomplete="off">
                </div>
            </div>

            <!-- Action group -->
            <div class="col-6 col-md-2">
                <label class="form-label text-muted mb-1" style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                    Category
                </label>
                <select name="action" id="auditAction" class="form-select form-select-sm">
                    <?php foreach ($actionGroups as $val => $label): ?>
                        <option value="<?= htmlspecialchars($val) ?>" <?= $fAction === $val ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- User filter -->
            <div class="col-6 col-md-2">
                <label class="form-label text-muted mb-1" style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                    Actor
                </label>
                <select name="user_id" id="auditUser" class="form-select form-select-sm">
                    <option value="">All Users</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= (int) $u['id'] ?>" <?= $fUserId === (int) $u['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date from -->
            <div class="col-6 col-md-2">
                <label class="form-label text-muted mb-1" style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                    From
                </label>
                <input type="date" name="date_from" id="auditDateFrom"
                       class="form-control form-control-sm"
                       value="<?= $fDateFrom ?>">
            </div>

            <!-- Date to -->
            <div class="col-6 col-md-1">
                <label class="form-label text-muted mb-1" style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                    To
                </label>
                <input type="date" name="date_to" id="auditDateTo"
                       class="form-control form-control-sm"
                       value="<?= $fDateTo ?>">
            </div>

            <!-- Buttons -->
            <div class="col-12 col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1" id="auditFilterBtn">
                    <i class="bi bi-funnel-fill"></i>
                </button>
                <?php if (!empty(array_filter($filters))): ?>
                <a href="<?= APP_URL ?>/admin/audit-logs"
                   class="btn btn-sm btn-outline-secondary"
                   id="auditClearBtn"
                   title="Clear filters">
                    <i class="bi bi-x-lg"></i>
                </a>
                <?php endif; ?>
            </div>

        </div><!-- /.row -->
    </form><!-- /.audit-filter-bar -->

    <!-- ── Table ──────────────────────────────────────────────────────────── -->
    <?php if (empty($logs)): ?>
        <div class="empty-state">
            <i class="bi bi-journal-x"></i>
            <div class="fw-semibold" style="color:#94a3b8;font-size:.9rem;">No log entries found</div>
            <div class="mt-1" style="font-size:.8rem;">
                <?php if (!empty(array_filter($filters))): ?>
                    Try adjusting or <a href="<?= APP_URL ?>/admin/audit-logs" class="text-primary">clearing</a> the filters.
                <?php else: ?>
                    Events will appear here as users interact with the system.
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table audit-table mb-0" id="auditTable">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th class="col-action">Action</th>
                    <th>Actor</th>
                    <th>Target</th>
                    <th class="col-detail">Detail</th>
                    <th>IP Address</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <?php
                    $detail = [];
                    if ($log['detail']) {
                        $detail = json_decode($log['detail'], true) ?? [];
                    }
                    $detailStr = '';
                    if (!empty($detail)) {
                        $parts = [];
                        foreach ($detail as $k => $v) {
                            if (is_array($v)) $v = json_encode($v);
                            $parts[] = '<span class="detail-pill">' . htmlspecialchars($k) . ': ' . htmlspecialchars((string) $v) . '</span>';
                        }
                        $detailStr = implode(' ', $parts);
                    }
                ?>
                <tr>
                    <!-- ID -->
                    <td class="text-muted" style="font-size:.75rem;"><?= (int) $log['id'] ?></td>

                    <!-- Action -->
                    <td class="col-action">
                        <span class="action-badge <?= auditBadgeClass($log['action']) ?>">
                            <i class="bi <?= auditIcon($log['action']) ?>"></i>
                            <?= htmlspecialchars($log['action']) ?>
                        </span>
                    </td>

                    <!-- Actor -->
                    <td>
                        <?php if ($log['actor_name'] && trim($log['actor_name']) !== ' '): ?>
                            <div class="actor-name"><?= htmlspecialchars(trim($log['actor_name'])) ?></div>
                            <div class="actor-email"><?= htmlspecialchars($log['actor_email'] ?? '') ?></div>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:.75rem;">System / Guest</span>
                        <?php endif; ?>
                    </td>

                    <!-- Target -->
                    <td>
                        <?php if ($log['target_type'] && $log['target_id']): ?>
                            <span style="font-size:.75rem;color:#94a3b8;">
                                <?= htmlspecialchars($log['target_type']) ?>
                                <span class="text-primary">#<?= (int) $log['target_id'] ?></span>
                            </span>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:.75rem;">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Detail -->
                    <td class="col-detail">
                        <?php if ($detailStr): ?>
                            <div style="display:flex;flex-wrap:wrap;gap:.25rem;">
                                <?= $detailStr ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- IP Address -->
                    <td>
                        <?php if ($log['ip_address']): ?>
                            <span class="ip-text"><?= htmlspecialchars($log['ip_address']) ?></span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Timestamp -->
                    <td>
                        <span class="ts-text" title="<?= htmlspecialchars($log['created_at']) ?>">
                            <?= date('d M Y', strtotime($log['created_at'])) ?><br>
                            <span style="color:#475569;"><?= date('H:i:s', strtotime($log['created_at'])) ?></span>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div><!-- /.table-responsive -->

    <!-- ── Pagination ─────────────────────────────────────────────────────── -->
    <?php if ($pages > 1): ?>
    <div class="d-flex align-items-center justify-content-between px-4 py-3"
         style="border-top:1px solid rgba(255,255,255,.06);">
        <div style="font-size:.775rem;color:#64748b;">
            Page <?= $page ?> of <?= $pages ?> &mdash; <?= number_format($total) ?> entries
        </div>
        <nav aria-label="Audit log pagination">
            <ul class="pagination pagination-sm audit-pagination mb-0">

                <!-- Previous -->
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link"
                       href="<?= APP_URL . '/admin/audit-logs?' . http_build_query(array_merge($filters, ['page' => $page - 1])) ?>"
                       aria-label="Previous">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>

                <?php
                // Show up to 7 page links centred around current page
                $window = 3;
                $start  = max(1, $page - $window);
                $end    = min($pages, $page + $window);
                if ($start > 1):
                ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= APP_URL . '/admin/audit-logs?' . http_build_query(array_merge($filters, ['page' => 1])) ?>">1</a>
                    </li>
                    <?php if ($start > 2): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($p = $start; $p <= $end; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link"
                       href="<?= APP_URL . '/admin/audit-logs?' . http_build_query(array_merge($filters, ['page' => $p])) ?>">
                        <?= $p ?>
                    </a>
                </li>
                <?php endfor; ?>

                <?php if ($end < $pages): ?>
                    <?php if ($end < $pages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= APP_URL . '/admin/audit-logs?' . http_build_query(array_merge($filters, ['page' => $pages])) ?>"><?= $pages ?></a>
                    </li>
                <?php endif; ?>

                <!-- Next -->
                <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link"
                       href="<?= APP_URL . '/admin/audit-logs?' . http_build_query(array_merge($filters, ['page' => $page + 1])) ?>"
                       aria-label="Next">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
    <?php endif; ?>

    <?php endif; // empty logs check ?>

</div><!-- /.audit-card -->

<script>
// Auto-submit filter form on select change
['auditAction', 'auditUser', 'auditDateFrom', 'auditDateTo'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('change', function() {
        document.getElementById('auditFilterForm').submit();
    });
});
</script>
