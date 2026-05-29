<?php
/**
 * Student Dashboard View — Phase 13
 */

$stats = $stats ?? [];
$total       = (int)($stats['total']        ?? 0);
$pending     = (int)($stats['pending']       ?? 0);
$underReview = (int)($stats['under_review']  ?? 0);
$resolved    = (int)($stats['resolved']      ?? 0);
$rejected    = (int)($stats['rejected']      ?? 0);
$closed      = (int)($stats['closed']        ?? 0);

function _student_rel(string $ts): string {
    $d = time() - strtotime($ts);
    if ($d < 60)    return 'Just now';
    if ($d < 3600)  return floor($d/60).'m ago';
    if ($d < 86400) return floor($d/3600).'h ago';
    return date('M j', strtotime($ts));
}

// Status chart data
$_statusColors = [
    'pending'      => '#fbbf24',
    'under_review' => '#818cf8',
    'resolved'     => '#34d399',
    'rejected'     => '#f87171',
    'closed'       => '#94a3b8',
];
$_chartLabels = [];
$_chartData   = [];
$_chartBg     = [];
$_statusMap   = [
    'pending'      => $pending,
    'under_review' => $underReview,
    'resolved'     => $resolved,
    'rejected'     => $rejected,
    'closed'       => $closed,
];
foreach ($_statusMap as $key => $val) {
    if ($val > 0) {
        $_chartLabels[] = ucfirst(str_replace('_', ' ', $key));
        $_chartData[]   = $val;
        $_chartBg[]     = $_statusColors[$key];
    }
}
?>

<!-- ── Welcome banner ───────────────────────────────────────────────────────── -->
<div class="dash-banner">
    <div class="dash-banner-icon" style="background:linear-gradient(135deg,#0891b2,#06b6d4);">
        <i class="bi bi-mortarboard-fill"></i>
    </div>
    <div>
        <div class="dash-banner-title">Hello, <?= htmlspecialchars($user['name'] ?? 'Student') ?></div>
        <div class="dash-banner-sub">
            <?php if ($total === 0): ?>
                You have no violation records. Keep it up! 🎉
            <?php elseif ($pending > 0): ?>
                You have <strong><?= $pending ?></strong> pending case<?= $pending !== 1 ? 's' : '' ?> under admin review.
            <?php else: ?>
                You have <strong><?= $total ?></strong> violation record<?= $total !== 1 ? 's' : '' ?> on file.
            <?php endif; ?>
        </div>
    </div>
    <?php if ($unreadCount > 0): ?>
    <div class="dash-banner-actions ms-auto">
        <a href="<?= APP_URL ?>/notifications" class="btn-dash-action btn-warning-dash" id="btnStudentNotifs">
            <i class="bi bi-bell-fill"></i> <?= $unreadCount ?> Unread
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- ── KPI cards ─────────────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <div class="col-6 col-sm-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                <i class="bi bi-file-earmark-text-fill"></i>
            </div>
            <div class="kpi-value"><?= $total ?></div>
            <div class="kpi-label">Total Cases</div>
        </div>
    </div>

    <div class="col-6 col-sm-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b);">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="kpi-value" style="color:#fbbf24;"><?= $pending ?></div>
            <div class="kpi-label">Pending</div>
        </div>
    </div>

    <div class="col-6 col-sm-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:linear-gradient(135deg,#4338ca,#818cf8);">
                <i class="bi bi-search"></i>
            </div>
            <div class="kpi-value" style="color:#818cf8;"><?= $underReview ?></div>
            <div class="kpi-label">Under Review</div>
        </div>
    </div>

    <div class="col-6 col-sm-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:linear-gradient(135deg,#059669,#10b981);">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="kpi-value" style="color:#34d399;"><?= $resolved ?></div>
            <div class="kpi-label">Resolved</div>
        </div>
    </div>

    <div class="col-6 col-sm-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:linear-gradient(135deg,#b91c1c,#f87171);">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <div class="kpi-value" style="color:#f87171;"><?= $rejected ?></div>
            <div class="kpi-label">Rejected</div>
        </div>
    </div>

    <div class="col-6 col-sm-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:linear-gradient(135deg,#475569,#94a3b8);">
                <i class="bi bi-lock-fill"></i>
            </div>
            <div class="kpi-value" style="color:#94a3b8;"><?= $closed ?></div>
            <div class="kpi-label">Closed</div>
        </div>
    </div>

</div>

<!-- ── Chart + History ────────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <!-- Status donut -->
    <div class="col-lg-4">
        <div class="chart-card h-100">
            <div class="chart-card-title"><i class="bi bi-pie-chart-fill me-2"></i>My Case Status</div>
            <?php if ($total === 0): ?>
            <div class="dash-empty">
                <i class="bi bi-shield-check" style="color:#34d399;"></i>
                <p style="color:#34d399;font-weight:600;">Clean record!</p>
                <p>No violations on file.</p>
            </div>
            <?php else: ?>
            <div style="position:relative;height:220px;">
                <canvas id="chartStudentStatus"></canvas>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Violation history table -->
    <div class="col-lg-8">
        <div class="dash-table-card h-100">
            <div class="dash-table-header">
                <span><i class="bi bi-clock-history me-2"></i>My Violation History</span>
                <a href="<?= APP_URL ?>/violations" class="dash-table-link" id="linkStudentAllViolations">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <?php if (empty($violations)): ?>
            <div class="dash-empty"><i class="bi bi-inbox"></i><p>No violation records found.</p></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Filed by</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($violations as $v): ?>
                        <tr onclick="location.href='<?= APP_URL ?>/violations/<?= $v['id'] ?>'" style="cursor:pointer;">
                            <td><code>#<?= $v['id'] ?></code></td>
                            <td><?= htmlspecialchars($v['type']) ?></td>
                            <td><span class="sev-badge sev-<?= $v['severity'] ?>"><?= ucfirst($v['severity']) ?></span></td>
                            <td><span class="status-badge status-<?= $v['status'] ?>"><?= ucfirst(str_replace('_',' ',$v['status'])) ?></span></td>
                            <td class="text-muted-sm"><?= htmlspecialchars($v['reporter_name'] ?? '—') ?></td>
                            <td class="text-muted-sm"><?= _student_rel($v['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ── Info card: student guidance ───────────────────────────────────────────── -->
<?php if ($total > 0 && $pending > 0): ?>
<div class="dash-info-card mb-4">
    <i class="bi bi-info-circle-fill" style="color:#818cf8;font-size:1.25rem;flex-shrink:0;"></i>
    <div>
        <strong>Case in progress.</strong> Your pending case<?= $pending > 1 ? 's are' : ' is' ?> currently under review by the compliance office. You will receive a notification when a decision is made. Contact your administrator for questions.
    </div>
</div>
<?php endif; ?>

<!-- ── Recent Notifications ──────────────────────────────────────────────────── -->
<?php include __DIR__ . '/_notifications_widget.php'; ?>

<!-- ── Shared dashboard CSS ──────────────────────────────────────────────────── -->
<?php include __DIR__ . '/_dashboard_styles.php'; ?>

<?php if ($total > 0 && !empty($_chartData)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#94a3b8';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
new Chart(document.getElementById('chartStudentStatus'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($_chartLabels) ?>,
        datasets: [{ data: <?= json_encode($_chartData) ?>, backgroundColor: <?= json_encode($_chartBg) ?>, borderWidth: 2, borderColor: '#0f172a' }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '62%',
        plugins: { legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } } }
    }
});
</script>
<?php endif; ?>
