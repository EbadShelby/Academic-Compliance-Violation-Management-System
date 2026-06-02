<?php

/**
 * Notifications — Full Inbox View
 *
 * Rendered inside views/layouts/main.php.
 *
 * Variables provided by NotificationController::index():
 *   $notifications  — array of all notification rows
 *   $unreadCount    — int
 */

/**
 * Return a relative time string from a MySQL timestamp.
 * e.g. "2 minutes ago", "1 hour ago", "3 days ago"
 */
function notif_relative_time(string $timestamp): string
{
    $diff = time() - strtotime($timestamp);
    if ($diff < 60)                  return 'Just now';
    if ($diff < 3600)                return floor($diff / 60)   . ' minute'  . (floor($diff / 60)   === 1 ? '' : 's') . ' ago';
    if ($diff < 86400)               return floor($diff / 3600) . ' hour'    . (floor($diff / 3600) === 1 ? '' : 's') . ' ago';
    if ($diff < 604800)              return floor($diff / 86400). ' day'     . (floor($diff / 86400)=== 1 ? '' : 's') . ' ago';
    return date('M j, Y', strtotime($timestamp));
}

/** Icon + colour map per notification type */
$typeConfig = [
    'info'    => ['icon' => 'bi-info-circle-fill',       'color' => '#818cf8', 'bg' => 'rgba(99,102,241,.12)',   'border' => 'rgba(99,102,241,.3)'],
    'success' => ['icon' => 'bi-check-circle-fill',      'color' => '#34d399', 'bg' => 'rgba(52,211,153,.12)',   'border' => 'rgba(52,211,153,.3)'],
    'warning' => ['icon' => 'bi-exclamation-triangle-fill','color' => '#fbbf24','bg' => 'rgba(251,191,36,.12)',   'border' => 'rgba(251,191,36,.3)'],
    'danger'  => ['icon' => 'bi-x-circle-fill',          'color' => '#f87171', 'bg' => 'rgba(248,113,113,.12)',  'border' => 'rgba(248,113,113,.3)'],
];

$authUser = Session::user();
?>

<style>
    /* ── Notification page ─────────────────────────────────────────────────── */
    .notif-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: .75rem;
    }
    .notif-title-area { display: flex; align-items: center; gap: .75rem; }
    .notif-count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px; height: 24px;
        padding: 0 6px;
        background: #f87171;
        border-radius: 12px;
        font-size: .75rem;
        font-weight: 700;
        color: #fff;
    }

    .notif-card {
        background: var(--surface-card);
        border: 1px solid var(--border-subtle);
        border-radius: .875rem;
        padding: 1.125rem 1.25rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: .75rem;
        transition: border-color .2s, background .2s;
        position: relative;
    }
    .notif-card.unread {
        border-color: rgba(79,70,229,.35);
        background: rgba(79,70,229,.07);
    }
    .notif-card:hover { border-color: rgba(165,180,252,.25); }

    .notif-icon-wrap {
        width: 42px; height: 42px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.125rem;
        flex-shrink: 0;
    }

    .notif-body { flex: 1; min-width: 0; }
    .notif-body-title {
        font-size: .9375rem;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: .2rem;
    }
    .notif-body-msg {
        font-size: .875rem;
        color: var(--text-muted);
        line-height: 1.5;
    }
    .notif-meta {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-top: .5rem;
        flex-wrap: wrap;
    }
    .notif-time { font-size: .8125rem; color: var(--text-muted); }
    .notif-unread-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #4f46e5;
        flex-shrink: 0;
    }

    .notif-actions { display: flex; align-items: center; gap: .5rem; flex-shrink: 0; }
    .btn-mark-read {
        background: none;
        border: 1px solid var(--border-subtle);
        border-radius: .5rem;
        color: var(--text-muted);
        font-size: .8125rem;
        padding: .25rem .625rem;
        cursor: pointer;
        transition: background .15s, color .15s, border-color .15s;
        white-space: nowrap;
    }
    .btn-mark-read:hover {
        background: rgba(79,70,229,.18);
        border-color: rgba(79,70,229,.4);
        color: #a5b4fc;
    }

    .notif-view-link {
        font-size: .8125rem;
        color: #818cf8;
        text-decoration: none;
    }
    .notif-view-link:hover { text-decoration: underline; }

    .notif-empty {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-muted);
    }
    .notif-empty i { font-size: 3.5rem; display: block; margin-bottom: 1rem; }
    .notif-empty p { font-size: .9375rem; margin: 0; }

    .mark-all-btn {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .4rem 1rem;
        background: rgba(79,70,229,.15);
        border: 1px solid rgba(79,70,229,.35);
        border-radius: .5rem;
        color: #a5b4fc;
        font-size: .875rem;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: background .15s, border-color .15s;
    }
    .mark-all-btn:hover {
        background: rgba(79,70,229,.28);
        border-color: rgba(79,70,229,.6);
    }
</style>

<!-- ── Page header ──────────────────────────────────────────────────────────── -->
<div class="notif-header">
    <div class="notif-title-area">
        <h2 style="font-size:1.125rem; font-weight:700; margin:0; color:var(--text-color);">Your Notifications</h2>
        <?php if ($unreadCount > 0): ?>
            <span class="notif-count-badge" id="unreadBadgePage"><?= $unreadCount ?></span>
        <?php endif; ?>
    </div>

    <?php if ($unreadCount > 0): ?>
    <form method="POST" action="<?= APP_URL ?>/notifications/read-all" id="markAllForm">
        <?= csrf_field() ?>
        <button type="submit" class="mark-all-btn" id="markAllBtn">
            <i class="bi bi-check2-all"></i> Mark all as read
        </button>
    </form>
    <?php endif; ?>
</div>

<!-- ── Notification list ────────────────────────────────────────────────────── -->
<?php if (empty($notifications)): ?>
<div class="notif-empty">
    <i class="bi bi-bell-slash"></i>
    <p>You have no notifications yet.</p>
</div>
<?php else: ?>

<div id="notifList">
<?php foreach ($notifications as $notif):
    $cfg     = $typeConfig[$notif['type']] ?? $typeConfig['info'];
    $isUnread = (int) $notif['is_read'] === 0;
    $relTime  = notif_relative_time($notif['created_at']);
?>
<div class="notif-card <?= $isUnread ? 'unread' : '' ?>" id="notif-<?= (int) $notif['id'] ?>">

    <!-- Icon -->
    <div class="notif-icon-wrap" style="background:<?= $cfg['bg'] ?>; border:1px solid <?= $cfg['border'] ?>;">
        <i class="bi <?= $cfg['icon'] ?>" style="color:<?= $cfg['color'] ?>;"></i>
    </div>

    <!-- Body -->
    <div class="notif-body">
        <div class="notif-body-title"><?= htmlspecialchars($notif['title']) ?></div>
        <div class="notif-body-msg"><?= htmlspecialchars($notif['message']) ?></div>
        <div class="notif-meta">
            <?php if ($isUnread): ?>
                <span class="notif-unread-dot" title="Unread"></span>
            <?php endif; ?>
            <span class="notif-time">
                <i class="bi bi-clock me-1"></i><?= htmlspecialchars($relTime) ?>
            </span>
            <?php if ($notif['reference_id'] && $notif['reference_table'] === 'violations'): ?>
                <a href="<?= APP_URL ?>/violations/<?= (int) $notif['reference_id'] ?>" class="notif-view-link">
                    View case <i class="bi bi-arrow-right ms-1"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mark read action -->
    <?php if ($isUnread): ?>
    <div class="notif-actions">
        <button
            class="btn-mark-read js-mark-read"
            data-id="<?= (int) $notif['id'] ?>"
            data-url="<?= APP_URL ?>/notifications/<?= (int) $notif['id'] ?>/read"
            title="Mark as read"
        >
            <i class="bi bi-check2 me-1"></i>Read
        </button>
    </div>
    <?php endif; ?>

</div>
<?php endforeach; ?>
</div><!-- /#notifList -->

<?php endif; ?>

<!-- ── JS: AJAX mark-read ────────────────────────────────────────────────────── -->
<script>
(function () {
    'use strict';

    /**
     * Update the unread badge count in both the topbar and the page header.
     */
    function decrementBadge() {
        [
            document.getElementById('notifBadgeTopbar'),
            document.getElementById('unreadBadgePage'),
        ].forEach(function (el) {
            if (!el) return;
            var n = parseInt(el.textContent, 10) - 1;
            if (n <= 0) {
                el.remove();
            } else {
                el.textContent = n;
            }
        });
    }

    function clearAllBadges() {
        [
            document.getElementById('notifBadgeTopbar'),
            document.getElementById('unreadBadgePage'),
        ].forEach(function (el) { if (el) el.remove(); });
    }

    // ── Single mark-read ────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-mark-read');
        if (!btn) return;

        e.preventDefault();
        var id  = btn.dataset.id;
        var url = btn.dataset.url;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch(url, {
            method:  'POST',
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken 
            },
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) return;

            var card = document.getElementById('notif-' + id);
            if (card) {
                card.classList.remove('unread');
                var dot = card.querySelector('.notif-unread-dot');
                if (dot) dot.remove();
                btn.closest('.notif-actions').remove();
            }
            decrementBadge();

            // Hide "Mark all" button if no more unread
            var badge = document.getElementById('unreadBadgePage');
            if (!badge) {
                var markAllForm = document.getElementById('markAllForm');
                if (markAllForm) markAllForm.remove();
            }
        })
        .catch(function () {
            // Fallback: just follow the link via form submit
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            document.body.appendChild(form);
            form.submit();
        });
    });

    // ── Mark all as read (AJAX) ─────────────────────────────────────────────
    var markAllForm = document.getElementById('markAllForm');
    if (markAllForm) {
        markAllForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch(markAllForm.action, {
                method:  'POST',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken 
                },
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) return;

                document.querySelectorAll('.notif-card.unread').forEach(function (card) {
                    card.classList.remove('unread');
                    var dot = card.querySelector('.notif-unread-dot');
                    if (dot) dot.remove();
                    var actions = card.querySelector('.notif-actions');
                    if (actions) actions.remove();
                });

                clearAllBadges();
                markAllForm.remove();
            })
            .catch(function () {
                markAllForm.submit();
            });
        });
    }
})();
</script>
