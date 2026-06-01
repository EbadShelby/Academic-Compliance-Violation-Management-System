<?php
/**
 * views/users/index.php
 * Admin — User Management list
 */
?>

<style>
/* ── Page-specific styles ─────────────────────────────────────────── */
.stat-card {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: 1rem;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: border-color .2s, transform .2s;
}
.stat-card:hover { border-color: rgba(79,70,229,.4); transform: translateY(-2px); }
.stat-icon {
    width: 48px; height: 48px;
    border-radius: .75rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.375rem; flex-shrink: 0;
}
.stat-icon.admin   { background: rgba(79,70,229,.15); color: #a5b4fc; }
.stat-icon.teacher { background: rgba(245,158,11,.15); color: #fcd34d; }
.stat-icon.student { background: rgba(16,185,129,.15); color: #6ee7b7; }
.stat-icon.total   { background: rgba(6,182,212,.15);  color: #67e8f9; }
.stat-label { font-size: .75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .06em; }
.stat-value { font-size: 1.625rem; font-weight: 700; line-height: 1.1; }

.users-table-wrap {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: 1rem;
    overflow: hidden;
}
.users-table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.125rem 1.5rem;
    border-bottom: 1px solid var(--border-subtle);
    gap: 1rem;
    flex-wrap: wrap;
}
.search-box {
    position: relative;
    flex: 1; min-width: 200px; max-width: 320px;
}
.search-box input {
    width: 100%;
    padding: .5rem .875rem .5rem 2.25rem;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--border-subtle);
    border-radius: .625rem;
    color: var(--text-primary);
    font-size: .875rem;
    outline: none;
    transition: border-color .15s;
}
.search-box input:focus { border-color: rgba(79,70,229,.6); }
.search-box input::placeholder { color: var(--text-muted); }
.search-box i {
    position: absolute; left: .75rem; top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted); font-size: .9375rem; pointer-events: none;
}

table.users-tbl { width: 100%; border-collapse: collapse; }
table.users-tbl thead th {
    padding: .75rem 1rem;
    font-size: .6875rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .08em;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border-subtle);
    white-space: nowrap;
}
table.users-tbl tbody tr {
    border-bottom: 1px solid var(--border-subtle);
    transition: background .15s;
}
table.users-tbl tbody tr:last-child { border-bottom: none; }
table.users-tbl tbody tr:hover { background: rgba(255,255,255,.025); }
table.users-tbl tbody td { padding: .875rem 1rem; font-size: .875rem; vertical-align: middle; }

.avatar-sm {
    width: 34px; height: 34px;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .75rem; font-weight: 700; color: #fff;
    flex-shrink: 0; text-transform: uppercase;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
}

.role-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .25rem .625rem; border-radius: 999px;
    font-size: .6875rem; font-weight: 600; text-transform: capitalize;
}
.role-badge.admin   { background: rgba(79,70,229,.15); color: #a5b4fc; }
.role-badge.teacher { background: rgba(245,158,11,.15); color: #fcd34d; }
.role-badge.student { background: rgba(16,185,129,.15); color: #6ee7b7; }

.status-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .25rem .625rem; border-radius: 999px;
    font-size: .6875rem; font-weight: 600;
}
.status-badge.active   { background: rgba(16,185,129,.12); color: #34d399; }
.status-badge.inactive { background: rgba(248,113,113,.12); color: #f87171; }

.action-btn {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .375rem .75rem;
    border-radius: .5rem;
    font-size: .8125rem; font-weight: 500;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    transition: background .15s, border-color .15s, color .15s;
    background: none;
}
.btn-view { color: #a5b4fc; border-color: rgba(165,180,252,.25); }
.btn-view:hover { background: rgba(79,70,229,.12); border-color: rgba(165,180,252,.5); color: #a5b4fc; }
.btn-edit { color: #fcd34d; border-color: rgba(252,211,77,.25); }
.btn-edit:hover { background: rgba(245,158,11,.12); border-color: rgba(252,211,77,.5); color: #fcd34d; }

.empty-state { padding: 3rem 1.5rem; text-align: center; color: var(--text-muted); }
.empty-state i { font-size: 2.5rem; margin-bottom: .75rem; display: block; }
</style>

<!-- ── Stats row ────────────────────────────────────────────────────────── -->
<?php
$totals    = ['admin' => 0, 'teacher' => 0, 'student' => 0, 'total' => count($users)];
foreach ($users as $u) {
    $slug = $u['role'] ?? '';
    if (isset($totals[$slug])) $totals[$slug]++;
}
?>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon total"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?= $totals['total'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon admin"><i class="bi bi-shield-check"></i></div>
            <div>
                <div class="stat-label">Admins</div>
                <div class="stat-value"><?= $totals['admin'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon teacher"><i class="bi bi-person-workspace"></i></div>
            <div>
                <div class="stat-label">Teachers</div>
                <div class="stat-value"><?= $totals['teacher'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon student"><i class="bi bi-mortarboard"></i></div>
            <div>
                <div class="stat-label">Students</div>
                <div class="stat-value"><?= $totals['student'] ?></div>
            </div>
        </div>
    </div>
</div>

<!-- ── Users table ─────────────────────────────────────────────────────── -->
<div class="users-table-wrap">
    <div class="users-table-header">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" id="userSearch" placeholder="Search users…" autocomplete="off">
        </div>
        <div class="d-flex gap-2">
            <a href="<?= APP_URL ?>/admin/users/import" class="action-btn" style="background:rgba(16,185,129,.15);border-color:rgba(16,185,129,.4);color:#6ee7b7;gap:.4rem;" id="importUsersBtn">
                <i class="bi bi-file-earmark-spreadsheet"></i> Import CSV
            </a>
            <a href="<?= APP_URL ?>/admin/users/create" class="action-btn" style="background:rgba(79,70,229,.15);border-color:rgba(79,70,229,.4);color:#a5b4fc;gap:.4rem;" id="createUserBtn">
                <i class="bi bi-plus-circle-fill"></i> Create User
            </a>
        </div>
    </div>

    <?php if (empty($users)): ?>
    <div class="empty-state">
        <i class="bi bi-people"></i>
        <p class="mb-0">No users found. <a href="<?= APP_URL ?>/admin/users/create" style="color:#a5b4fc;">Create the first one.</a></p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="users-tbl" id="usersTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Student ID</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $i => $u): ?>
                <tr>
                    <td class="text-muted" style="font-size:.75rem;"><?= $u['id'] ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm"><?= mb_substr($u['first_name'], 0, 1) ?></div>
                            <div>
                                <div style="font-weight:600;"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="role-badge <?= htmlspecialchars($u['role'] ?? '') ?>">
                            <?= htmlspecialchars($u['role_name'] ?? $u['role'] ?? '—') ?>
                        </span>
                    </td>
                    <td style="color:var(--text-muted);"><?= $u['student_id'] ? htmlspecialchars($u['student_id']) : '<span style="color:rgba(255,255,255,.2);">—</span>' ?></td>
                    <td>
                        <?php if ((int)$u['is_active'] === 1): ?>
                        <span class="status-badge active"><i class="bi bi-circle-fill" style="font-size:.45rem;"></i> Active</span>
                        <?php else: ?>
                        <span class="status-badge inactive"><i class="bi bi-circle-fill" style="font-size:.45rem;"></i> Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td style="color:var(--text-muted);font-size:.8125rem;">
                        <?= date('M j, Y', strtotime($u['created_at'])) ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?= APP_URL ?>/admin/users/<?= $u['id'] ?>" class="action-btn btn-view" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?= APP_URL ?>/admin/users/<?= $u['id'] ?>/edit" class="action-btn btn-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
// ── Live search ────────────────────────────────────────────────────────────
(function () {
    const input = document.getElementById('userSearch');
    if (!input) return;

    input.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('#usersTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
})();
</script>
