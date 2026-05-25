<?php
/**
 * Dashboard — main overview page.
 * Rendered inside views/layouts/main.php.
 */

$authUser = Session::user();
$role     = strtolower($authUser['role'] ?? 'student');
?>

<!-- Welcome banner -->
<div style="
    background: linear-gradient(135deg, rgba(79,70,229,.25), rgba(124,58,237,.18));
    border: 1px solid rgba(79,70,229,.3);
    border-radius: 1rem;
    padding: 1.75rem 2rem;
    margin-bottom: 1.75rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
">
    <div style="
        width: 52px; height: 52px;
        border-radius: .875rem;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; color: #fff; flex-shrink: 0;
        box-shadow: 0 8px 24px rgba(79,70,229,.4);
    ">
        <?php if ($role === 'admin'): ?>
            <i class="bi bi-star-fill"></i>
        <?php elseif ($role === 'teacher'): ?>
            <i class="bi bi-person-video2"></i>
        <?php else: ?>
            <i class="bi bi-mortarboard-fill"></i>
        <?php endif; ?>
    </div>
    <div>
        <h2 style="font-size:1.25rem; font-weight:700; margin:0 0 .2rem; color:#f8fafc;">
            Welcome back, <?= htmlspecialchars($authUser['name'] ?? 'User') ?>
        </h2>
        <p style="font-size:.875rem; color:#94a3b8; margin:0;">
            You're signed in as <strong style="color:#a5b4fc; text-transform:capitalize;"><?= htmlspecialchars($role) ?></strong>.
            <?php if ($role === 'admin'): ?>
                You have full system access.
            <?php elseif ($role === 'teacher'): ?>
                You can file and manage violation reports.
            <?php else: ?>
                You can view your own violation records.
            <?php endif; ?>
        </p>
    </div>
</div>

<!-- Quick-access cards -->
<div class="row g-3">

    <div class="col-sm-6 col-xl-3">
        <div style="background:#1e293b; border:1px solid rgba(255,255,255,.08); border-radius:.875rem; padding:1.25rem;">
            <div style="font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin-bottom:.75rem;">
                <i class="bi bi-exclamation-triangle me-1"></i> Violations
            </div>
            <div style="font-size:2rem; font-weight:700; color:#f8fafc; line-height:1;">—</div>
            <a href="<?= APP_URL ?>/violations" style="font-size:.8125rem; color:#818cf8; text-decoration:none; display:inline-block; margin-top:.5rem;">
                View all <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>

    <?php if ($role === 'admin' || $role === 'teacher'): ?>
    <div class="col-sm-6 col-xl-3">
        <div style="background:#1e293b; border:1px solid rgba(255,255,255,.08); border-radius:.875rem; padding:1.25rem;">
            <div style="font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin-bottom:.75rem;">
                <i class="bi bi-plus-circle me-1"></i> File Report
            </div>
            <div style="font-size:.9375rem; font-weight:600; color:#f8fafc; line-height:1.3;">Submit a new violation</div>
            <a href="<?= APP_URL ?>/violations/create" style="font-size:.8125rem; color:#818cf8; text-decoration:none; display:inline-block; margin-top:.5rem;">
                Create <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($role === 'admin'): ?>
    <div class="col-sm-6 col-xl-3">
        <div style="background:#1e293b; border:1px solid rgba(255,255,255,.08); border-radius:.875rem; padding:1.25rem;">
            <div style="font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin-bottom:.75rem;">
                <i class="bi bi-people me-1"></i> Users
            </div>
            <div style="font-size:2rem; font-weight:700; color:#f8fafc; line-height:1;">—</div>
            <a href="<?= APP_URL ?>/admin/users" style="font-size:.8125rem; color:#818cf8; text-decoration:none; display:inline-block; margin-top:.5rem;">
                Manage <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div style="background:#1e293b; border:1px solid rgba(255,255,255,.08); border-radius:.875rem; padding:1.25rem;">
            <div style="font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin-bottom:.75rem;">
                <i class="bi bi-journal-text me-1"></i> Audit Logs
            </div>
            <div style="font-size:.9375rem; font-weight:600; color:#f8fafc; line-height:1.3;">Review system activity</div>
            <a href="<?= APP_URL ?>/admin/audit-logs" style="font-size:.8125rem; color:#818cf8; text-decoration:none; display:inline-block; margin-top:.5rem;">
                View logs <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <?php endif; ?>

</div>
