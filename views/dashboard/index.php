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

<!-- ── Recent Notifications widget ───────────────────────────────────────────── -->
<?php
$recentNotifications = $recentNotifications ?? [];
$unreadCount         = $unreadCount         ?? 0;

// Type icon / colour map (inline — no external dependency)
$_dTypeConfig = [
    'info'    => ['icon' => 'bi-info-circle-fill',        'color' => '#818cf8'],
    'success' => ['icon' => 'bi-check-circle-fill',       'color' => '#34d399'],
    'warning' => ['icon' => 'bi-exclamation-triangle-fill','color' => '#fbbf24'],
    'danger'  => ['icon' => 'bi-x-circle-fill',           'color' => '#f87171'],
];
function _dash_rel_time(string $ts): string {
    $d = time() - strtotime($ts);
    if ($d < 60)    return 'Just now';
    if ($d < 3600)  return floor($d/60)   . 'm ago';
    if ($d < 86400) return floor($d/3600) . 'h ago';
    return date('M j', strtotime($ts));
}
?>

<div style="margin-top:1.75rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem;">
        <div style="display:flex;align-items:center;gap:.75rem;">
            <span style="font-size:1rem;font-weight:700;color:#f8fafc;">Recent Notifications</span>
            <?php if ($unreadCount > 0): ?>
            <span style="background:#f87171;color:#fff;font-size:.6875rem;font-weight:700;padding:2px 7px;border-radius:9px;"><?= $unreadCount ?> unread</span>
            <?php endif; ?>
        </div>
        <a href="<?= APP_URL ?>/notifications" style="font-size:.8125rem;color:#818cf8;text-decoration:none;">
            View all <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <?php if (empty($recentNotifications)): ?>
    <div style="background:#1e293b;border:1px solid rgba(255,255,255,.08);border-radius:.875rem;padding:2rem;text-align:center;color:#475569;">
        <i class="bi bi-bell-slash" style="font-size:2rem;display:block;margin-bottom:.75rem;"></i>
        <p style="margin:0;font-size:.875rem;">No notifications yet.</p>
    </div>
    <?php else: ?>
    <div style="background:#1e293b;border:1px solid rgba(255,255,255,.08);border-radius:.875rem;overflow:hidden;">
        <?php foreach ($recentNotifications as $i => $_notif):
            $_cfg     = $_dTypeConfig[$_notif['type']] ?? $_dTypeConfig['info'];
            $_isUnread = (int) $_notif['is_read'] === 0;
        ?>
        <div style="
            display:flex;align-items:center;gap:.875rem;
            padding:.875rem 1.125rem;
            <?= $i < count($recentNotifications) - 1 ? 'border-bottom:1px solid rgba(255,255,255,.06);' : '' ?>
            <?= $_isUnread ? 'background:rgba(79,70,229,.06);' : '' ?>
            transition:background .15s;
        ">
            <i class="bi <?= $_cfg['icon'] ?>" style="color:<?= $_cfg['color'] ?>;font-size:1.0625rem;flex-shrink:0;"></i>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.875rem;font-weight:<?= $_isUnread ? '600' : '400' ?>;color:<?= $_isUnread ? '#f8fafc' : '#94a3b8' ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <?= htmlspecialchars($_notif['title']) ?>
                </div>
                <div style="font-size:.8125rem;color:#64748b;margin-top:.1rem;">
                    <?= _dash_rel_time($_notif['created_at']) ?>
                </div>
            </div>
            <?php if ($_isUnread): ?>
            <span style="width:8px;height:8px;border-radius:50%;background:#4f46e5;flex-shrink:0;"></span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
