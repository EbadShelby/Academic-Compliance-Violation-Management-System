<?php
/**
 * Shared notifications widget partial for all dashboard views.
 *
 * Expects the parent view to have set:
 *   $recentNotifications  — array of notification rows (up to 5)
 *   $unreadCount          — int
 */

$recentNotifications = $recentNotifications ?? [];
$unreadCount         = $unreadCount         ?? 0;

$_ntTypeConfig = [
    'info'    => ['icon' => 'bi-info-circle-fill',         'color' => '#818cf8'],
    'success' => ['icon' => 'bi-check-circle-fill',        'color' => '#34d399'],
    'warning' => ['icon' => 'bi-exclamation-triangle-fill','color' => '#fbbf24'],
    'danger'  => ['icon' => 'bi-x-circle-fill',            'color' => '#f87171'],
];

function _notif_widget_rel(string $ts): string {
    $d = time() - strtotime($ts);
    if ($d < 60)    return 'Just now';
    if ($d < 3600)  return floor($d/60).'m ago';
    if ($d < 86400) return floor($d/3600).'h ago';
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
        <a href="<?= APP_URL ?>/notifications" style="font-size:.8125rem;color:#818cf8;text-decoration:none;" id="notifWidgetViewAll">
            View all <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <?php if (empty($recentNotifications)): ?>
    <div class="dash-empty" style="background:#1e293b;border:1px solid rgba(255,255,255,.08);border-radius:.875rem;">
        <i class="bi bi-bell-slash"></i>
        <p style="margin:0;">No notifications yet.</p>
    </div>
    <?php else: ?>
    <div style="background:#1e293b;border:1px solid rgba(255,255,255,.08);border-radius:.875rem;overflow:hidden;">
        <?php foreach ($recentNotifications as $i => $_notif):
            $_cfg      = $_ntTypeConfig[$_notif['type']] ?? $_ntTypeConfig['info'];
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
                <div style="font-size:.8125rem;color:#64748b;margin-top:.1rem;"><?= _notif_widget_rel($_notif['created_at']) ?></div>
            </div>
            <?php if ($_isUnread): ?>
            <span style="width:8px;height:8px;border-radius:50%;background:#4f46e5;flex-shrink:0;"></span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
