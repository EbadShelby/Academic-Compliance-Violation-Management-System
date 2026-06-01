<?php
/**
 * views/violations/index.php
 * Violations list — Admin, Teacher, Student
 */
$authUser = Session::user();
?>

<style>
/* ── Violations index ──────────────────────────────────────────────────── */
.violations-table-wrap {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: 1rem;
    overflow: hidden;
}
.violations-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .75rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border-subtle);
}
.search-box-wrap { position: relative; }
.search-box-wrap i {
    position: absolute;
    left: .75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: .9rem;
    pointer-events: none;
}
.search-input {
    padding: .5rem .875rem .5rem 2.25rem;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--border-subtle);
    border-radius: .5rem;
    color: var(--text-primary);
    font-size: .875rem;
    width: 240px;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.search-input:focus { border-color: var(--brand-primary); box-shadow: 0 0 0 3px rgba(79,70,229,.2); }

.viol-table { width: 100%; border-collapse: collapse; }
.viol-table thead tr { background: rgba(255,255,255,.03); }
.viol-table th {
    padding: .75rem 1rem;
    font-size: .75rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .07em;
    white-space: nowrap;
    border-bottom: 1px solid var(--border-subtle);
}
.viol-table td {
    padding: .875rem 1rem;
    font-size: .875rem;
    border-bottom: 1px solid rgba(255,255,255,.04);
    color: var(--text-primary);
    vertical-align: middle;
}
.viol-table tbody tr:last-child td { border-bottom: none; }
.viol-table tbody tr { transition: background .12s; }
.viol-table tbody tr:hover { background: rgba(255,255,255,.025); }

/* Badges */
.badge-sev, .badge-status {
    display: inline-block;
    padding: .2rem .6rem;
    border-radius: .375rem;
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    white-space: nowrap;
}
.sev-minor    { background: rgba(52,211,153,.15);  color: #34d399; }
.sev-moderate { background: rgba(251,191,36,.15);   color: #fbbf24; }
.sev-major    { background: rgba(249,115,22,.15);   color: #f97316; }
.sev-critical { background: rgba(248,113,113,.15);  color: #f87171; }

.status-pending       { background: rgba(99,102,241,.15);  color: #a5b4fc; }
.status-open          { background: rgba(99,102,241,.15);  color: #a5b4fc; }
.status-under_review  { background: rgba(251,191,36,.15);  color: #fbbf24; }
.status-resolved      { background: rgba(52,211,153,.15);  color: #34d399; }
.status-rejected      { background: rgba(248,113,113,.15); color: #f87171; }
.status-dismissed     { background: rgba(248,113,113,.15); color: #f87171; }
.status-closed        { background: rgba(148,163,184,.1);  color: #94a3b8; }

.btn-view-link {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .35rem .75rem;
    border-radius: .5rem;
    font-size: .8rem;
    font-weight: 600;
    color: #a5b4fc;
    background: rgba(99,102,241,.12);
    text-decoration: none;
    transition: background .15s;
}
.btn-view-link:hover { background: rgba(99,102,241,.25); color: #c7d2fe; }

.btn-review-link {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .35rem .75rem;
    border-radius: .5rem;
    font-size: .8rem;
    font-weight: 600;
    color: #fbbf24;
    background: rgba(251,191,36,.1);
    text-decoration: none;
    transition: background .15s;
}
.btn-review-link:hover { background: rgba(251,191,36,.22); color: #fde68a; }

.empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    color: var(--text-muted);
}
.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; opacity: .4; }

.btn-file-violation {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .5rem 1.125rem;
    background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
    border: none;
    border-radius: .625rem;
    color: #fff;
    font-size: .875rem;
    font-weight: 700;
    text-decoration: none;
    transition: opacity .15s;
}
.btn-file-violation:hover { opacity: .88; color: #fff; }

/* Pagination */
.app-pagination .page-link {
    background: rgba(255,255,255,.04);
    border-color: rgba(255,255,255,.1);
    color: var(--text-muted);
    font-size: .8rem;
}
.app-pagination .page-item.active .page-link {
    background: var(--brand-primary);
    border-color: var(--brand-primary);
    color: #fff;
}
.app-pagination .page-item.disabled .page-link { opacity: .4; }
</style>

<!-- ── Toolbar ─────────────────────────────────────────────────────────── -->
<div class="violations-table-wrap">
    <div class="violations-toolbar">
        <div class="search-box-wrap">
            <i class="bi bi-search"></i>
            <input type="text" class="search-input" id="violSearch" placeholder="Search violations…" autocomplete="off">
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if (isAdmin()): ?>
            <a href="<?= APP_URL ?>/violations/export" class="btn btn-sm btn-outline-secondary" style="border-radius:.625rem; padding:.5rem 1rem;">
                <i class="bi bi-download"></i> Export CSV
            </a>
            <?php endif; ?>
            <?php if (isTeacher() || isAdmin()): ?>
            <a href="<?= APP_URL ?>/violations/create" class="btn-file-violation">
                <i class="bi bi-plus-circle"></i> File Violation
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Table ───────────────────────────────────────────────────────── -->
    <?php if (empty($violations)): ?>
    <div class="empty-state">
        <i class="bi bi-exclamation-triangle"></i>
        <p class="mb-1 fw-semibold">No violations found</p>
        <p class="mb-0" style="font-size:.875rem;">
            <?php if (isTeacher() || isAdmin()): ?>
                File a new violation report to get started.
            <?php else: ?>
                You have no violation records at this time.
            <?php endif; ?>
        </p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="viol-table" id="violTable">
            <thead>
                <tr>
                    <th>#</th>
                    <?php if (!isStudent()): ?><th>Student</th><?php endif; ?>
                    <th>Category</th>
                    <th>Severity</th>
                    <th>Status</th>
                    <th>Incident Date</th>
                    <?php if (!isStudent()): ?><th>Reporter</th><?php endif; ?>
                    <th></th>
                </tr>
            </thead>
            <tbody id="violTbody">
            <?php foreach ($violations as $v): ?>
            <tr>
                <td class="text-muted" style="font-size:.8rem;">#<?= $v['id'] ?></td>
                <?php if (!isStudent()): ?>
                <td>
                    <div style="font-weight:600;"><?= htmlspecialchars($v['student_name'] ?? '—') ?></div>
                    <?php if (!empty($v['student_number'])): ?>
                    <div style="font-size:.75rem;color:var(--text-muted);"><?= htmlspecialchars($v['student_number']) ?></div>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
                <td><?= htmlspecialchars($v['type']) ?></td>
                <td>
                    <span class="badge-sev sev-<?= htmlspecialchars($v['severity']) ?>">
                        <?= htmlspecialchars($v['severity']) ?>
                    </span>
                </td>
                <td>
                    <span class="badge-status status-<?= htmlspecialchars($v['status']) ?>">
                        <?= htmlspecialchars(str_replace('_', ' ', $v['status'])) ?>
                    </span>
                </td>
                <td style="white-space:nowrap;"><?= htmlspecialchars($v['incident_date']) ?></td>
                <?php if (!isStudent()): ?>
                <td style="font-size:.825rem;color:var(--text-muted);"><?= htmlspecialchars($v['reporter_name'] ?? '—') ?></td>
                <?php endif; ?>
                <td>
                    <div style="display:flex;align-items:center;gap:.4rem;">
                    <a href="<?= APP_URL ?>/violations/<?= $v['id'] ?>" class="btn-view-link">
                        <i class="bi bi-eye"></i> View
                    </a>
                    <?php if (isAdmin()): ?>
                    <a href="<?= APP_URL ?>/violations/<?= $v['id'] ?>/review" class="btn-review-link">
                        <i class="bi bi-clipboard2-check"></i> Review
                    </a>
                    <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- ── Pagination ─────────────────────────────────────────────────────── -->
    <?php if ($pages > 1): ?>
    <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-top:1px solid var(--border-subtle);">
        <div style="font-size:.775rem;color:var(--text-muted);">
            Page <?= $page ?> of <?= $pages ?> &mdash; <?= number_format($total) ?> entries
        </div>
        <nav aria-label="Violation pagination">
            <ul class="pagination pagination-sm app-pagination mb-0">
                <!-- Previous -->
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= APP_URL . '/violations?page=' . ($page - 1) ?>" aria-label="Previous">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>

                <?php
                $window = 3;
                $start  = max(1, $page - $window);
                $end    = min($pages, $page + $window);
                if ($start > 1):
                ?>
                    <li class="page-item"><a class="page-link" href="<?= APP_URL . '/violations?page=1' ?>">1</a></li>
                    <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                <?php endif; ?>

                <?php for ($p = $start; $p <= $end; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= APP_URL . '/violations?page=' . $p ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($end < $pages): ?>
                    <?php if ($end < $pages - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                    <li class="page-item"><a class="page-link" href="<?= APP_URL . '/violations?page=' . $pages ?>"><?= $pages ?></a></li>
                <?php endif; ?>

                <!-- Next -->
                <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= APP_URL . '/violations?page=' . ($page + 1) ?>" aria-label="Next">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<script>
(function () {
    const searchInput = document.getElementById('violSearch');
    const tbody       = document.getElementById('violTbody');

    if (!searchInput || !tbody) return;

    searchInput.addEventListener('input', function () {
        const q   = this.value.toLowerCase().trim();
        const rows = tbody.querySelectorAll('tr');
        rows.forEach(row => {
            row.style.display = q === '' || row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
})();
</script>
