<?php
/**
 * views/users/show.php
 * Admin — User Profile / Detail view
 */
?>

<style>
.profile-card {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: 1rem;
    overflow: hidden;
}
.profile-header {
    padding: 2rem;
    background: linear-gradient(135deg, rgba(79,70,229,.15), rgba(124,58,237,.08));
    border-bottom: 1px solid var(--border-subtle);
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.profile-avatar {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.625rem; font-weight: 700; color: #fff;
    text-transform: uppercase; flex-shrink: 0;
}
.profile-name { font-size: 1.3125rem; font-weight: 700; margin-bottom: .25rem; }
.profile-email { font-size: .9375rem; color: var(--text-muted); margin-bottom: .5rem; }

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1.25rem;
    padding: 1.75rem 2rem;
}
.info-item {}
.info-item-label { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--text-muted); margin-bottom: .3rem; }
.info-item-value { font-size: .9375rem; color: var(--text-primary); font-weight: 500; }

.role-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .3rem .75rem; border-radius: 999px;
    font-size: .8125rem; font-weight: 600; text-transform: capitalize;
}
.role-badge.admin   { background: rgba(79,70,229,.15); color: #a5b4fc; }
.role-badge.teacher { background: rgba(245,158,11,.15); color: #fcd34d; }
.role-badge.student { background: rgba(16,185,129,.15); color: #6ee7b7; }

.status-badge {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .3rem .75rem; border-radius: 999px;
    font-size: .8125rem; font-weight: 600;
}
.status-badge.active   { background: rgba(16,185,129,.12); color: #34d399; }
.status-badge.inactive { background: rgba(248,113,113,.12); color: #f87171; }

.action-panel {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: 1rem;
    overflow: hidden;
}
.action-panel-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-subtle);
    font-size: .6875rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .08em;
    color: var(--text-muted);
}
.action-panel-body { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: .75rem; }

.action-form-btn {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .625rem 1.125rem;
    border-radius: .625rem;
    font-size: .875rem; font-weight: 500;
    border: 1px solid transparent;
    cursor: pointer; text-decoration: none;
    transition: background .15s, opacity .15s;
    width: 100%; justify-content: center;
    background: none;
}
.btn-edit-full {
    background: rgba(245,158,11,.12); border-color: rgba(245,158,11,.35); color: #fcd34d;
}
.btn-edit-full:hover { opacity: .85; color: #fcd34d; }
.btn-deactivate {
    background: rgba(248,113,113,.1); border-color: rgba(248,113,113,.35); color: #f87171;
}
.btn-deactivate:hover { opacity: .85; }
.btn-reactivate {
    background: rgba(16,185,129,.1); border-color: rgba(16,185,129,.35); color: #34d399;
}
.btn-reactivate:hover { opacity: .85; }
.btn-back {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5rem 1rem;
    border-radius: .5rem;
    border: 1px solid var(--border-subtle);
    color: var(--text-muted); font-size: .875rem; font-weight: 500;
    text-decoration: none; background: none;
    transition: background .15s, color .15s;
}
.btn-back:hover { background: rgba(255,255,255,.05); color: var(--text-primary); }
</style>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" style="margin-bottom:1.25rem;">
    <ol class="d-flex gap-2 list-unstyled mb-0" style="font-size:.875rem;color:var(--text-muted);">
        <li><a href="<?= APP_URL ?>/admin/users" style="color:var(--text-muted);text-decoration:none;">Users</a></li>
        <li style="opacity:.4;">/</li>
        <li style="color:var(--text-primary);"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></li>
    </ol>
</nav>

<div class="row g-3">

    <!-- Profile card -->
    <div class="col-lg-8">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar"><?= mb_substr($user['first_name'], 0, 1) ?></div>
                <div class="flex-grow-1">
                    <div class="profile-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
                    <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="role-badge <?= htmlspecialchars($user['role'] ?? '') ?>">
                            <?= htmlspecialchars($user['role_name'] ?? $user['role'] ?? '—') ?>
                        </span>
                        <?php if ((int)$user['is_active'] === 1): ?>
                        <span class="status-badge active"><i class="bi bi-circle-fill" style="font-size:.4rem;"></i> Active</span>
                        <?php else: ?>
                        <span class="status-badge inactive"><i class="bi bi-circle-fill" style="font-size:.4rem;"></i> Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="<?= APP_URL ?>/admin/users" class="btn-back ms-auto"><i class="bi bi-arrow-left"></i> Back</a>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-item-label">User ID</div>
                    <div class="info-item-value">#<?= $user['id'] ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">First Name</div>
                    <div class="info-item-value"><?= htmlspecialchars($user['first_name']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Last Name</div>
                    <div class="info-item-value"><?= htmlspecialchars($user['last_name']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Email</div>
                    <div class="info-item-value" style="word-break:break-all;"><?= htmlspecialchars($user['email']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Role</div>
                    <div class="info-item-value">
                        <span class="role-badge <?= htmlspecialchars($user['role'] ?? '') ?>">
                            <?= htmlspecialchars($user['role_name'] ?? '—') ?>
                        </span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Student ID</div>
                    <div class="info-item-value"><?= $user['student_id'] ? htmlspecialchars($user['student_id']) : '<span style="color:var(--text-muted);">N/A</span>' ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Account Status</div>
                    <div class="info-item-value">
                        <?php if ((int)$user['is_active'] === 1): ?>
                        <span class="status-badge active"><i class="bi bi-circle-fill" style="font-size:.4rem;"></i> Active</span>
                        <?php else: ?>
                        <span class="status-badge inactive"><i class="bi bi-circle-fill" style="font-size:.4rem;"></i> Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Member Since</div>
                    <div class="info-item-value"><?= date('F j, Y', strtotime($user['created_at'])) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Last Updated</div>
                    <div class="info-item-value"><?= date('F j, Y', strtotime($user['updated_at'])) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action panel -->
    <div class="col-lg-4">
        <div class="action-panel">
            <div class="action-panel-header">Actions</div>
            <div class="action-panel-body">

                <!-- Edit -->
                <a href="<?= APP_URL ?>/admin/users/<?= $user['id'] ?>/edit" class="action-form-btn btn-edit-full">
                    <i class="bi bi-pencil-fill"></i> Edit User
                </a>

                <!-- Deactivate / Reactivate -->
                <?php if ((int)$user['is_active'] === 1): ?>
                <form method="POST" action="<?= APP_URL ?>/admin/users/<?= $user['id'] ?>/delete"
                    onsubmit="return confirm('Deactivate this user? They will not be able to log in.')">
                    <input type="hidden" name="_action" value="deactivate">
                    <button type="submit" class="action-form-btn btn-deactivate">
                        <i class="bi bi-person-dash-fill"></i> Deactivate Account
                    </button>
                </form>
                <?php else: ?>
                <form method="POST" action="<?= APP_URL ?>/admin/users/<?= $user['id'] ?>/delete"
                    onsubmit="return confirm('Reactivate this user?')">
                    <input type="hidden" name="_action" value="reactivate">
                    <button type="submit" class="action-form-btn btn-reactivate">
                        <i class="bi bi-person-check-fill"></i> Reactivate Account
                    </button>
                </form>
                <?php endif; ?>

            </div>
        </div>
    </div>

</div>
