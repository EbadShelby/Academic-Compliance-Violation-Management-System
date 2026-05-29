<?php
/**
 * Admin Dashboard View — Phase 13
 * Rendered inside views/layouts/main.php ($content).
 */

// ── helpers ──────────────────────────────────────────────────────────────────
$statusColors = [
    'pending'      => '#fbbf24',
    'under_review' => '#818cf8',
    'resolved'     => '#34d399',
    'rejected'     => '#f87171',
    'closed'       => '#94a3b8',
];
$severityColors = [
    'minor'    => '#34d399',
    'moderate' => '#fbbf24',
    'major'    => '#f97316',
    'critical' => '#f87171',
];

function _admin_rel($ts): string {
    $d = time() - strtotime($ts);
    if ($d < 60)    return 'Just now';
    if ($d < 3600)  return floor($d/60).'m ago';
    if ($d < 86400) return floor($d/3600).'h ago';
    return date('M j', strtotime($ts));
}

// ── user totals ───────────────────────────────────────────────────────────────
$totalUsers    = 0;
$totalStudents = 0;
$totalTeachers = 0;
foreach (($userCounts ?? []) as $uc) {
    $totalUsers += (int) $uc['total'];
    if ($uc['slug'] === 'student') $totalStudents = (int) $uc['total'];
    if ($uc['slug'] === 'teacher') $totalTeachers = (int) $uc['total'];
}

// ── chart data: status distribution ──────────────────────────────────────────
$_statusLabels = [];
$_statusData   = [];
$_statusBg     = [];
foreach (($statusDist ?? []) as $row) {
    $_statusLabels[] = ucfirst(str_replace('_', ' ', $row['status']));
    $_statusData[]   = (int) $row['total'];
    $_statusBg[]     = $statusColors[$row['status']] ?? '#64748b';
}

// ── chart data: category distribution ────────────────────────────────────────
$_catLabels = [];
$_catData   = [];
$_catBg     = ['#818cf8','#34d399','#fbbf24','#f87171','#f97316','#06b6d4','#a78bfa','#94a3b8'];
foreach (($categoryDist ?? []) as $i => $row) {
    $_catLabels[] = $row['category'];
    $_catData[]   = (int) $row['total'];
}

// ── chart data: monthly trend ─────────────────────────────────────────────────
$_monthLabels = [];
$_monthData   = [];
foreach (($monthlyTrend ?? []) as $row) {
    $_monthLabels[] = $row['month'];
    $_monthData[]   = (int) $row['total'];
}

// ── chart data: severity ──────────────────────────────────────────────────────
$_sevLabels = [];
$_sevData   = [];
$_sevBg     = [];
foreach (($severityDist ?? []) as $row) {
    $_sevLabels[] = ucfirst($row['severity']);
    $_sevData[]   = (int) $row['total'];
    $_sevBg[]     = $severityColors[$row['severity']] ?? '#64748b';
}
?>

<!-- ── Welcome banner ───────────────────────────────────────────────────────── -->
<div class="dash-banner">
    <div class="dash-banner-icon"><i class="bi bi-star-fill"></i></div>
    <div>
        <div class="dash-banner-title">Welcome back, <?= htmlspecialchars($user['name'] ?? 'Admin') ?></div>
        <div class="dash-banner-sub">You have <strong><?= $pendingCases ?? 0 ?></strong> pending case<?= ($pendingCases ?? 0) !== 1 ? 's' : '' ?> awaiting review.</div>
    </div>
    <div class="dash-banner-actions ms-auto d-flex gap-2 flex-wrap">
        <a href="<?= APP_URL ?>/violations/create" class="btn-dash-action btn-primary-dash" id="btnAdminFileViolation">
            <i class="bi bi-plus-circle"></i> File Violation
        </a>
        <a href="<?= APP_URL ?>/violations?status=pending" class="btn-dash-action btn-warning-dash" id="btnAdminReviewPending">
            <i class="bi bi-hourglass-split"></i> Review Pending
        </a>
        <a href="<?= APP_URL ?>/admin/users" class="btn-dash-action btn-neutral-dash" id="btnAdminManageUsers">
            <i class="bi bi-people"></i> Manage Users
        </a>
    </div>
</div>

<!-- ── KPI stat cards ────────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="kpi-value"><?= number_format($totalViolations ?? 0) ?></div>
            <div class="kpi-label">Total Violations</div>
            <a href="<?= APP_URL ?>/violations" class="kpi-link" id="kpiLinkTotalViolations">View all <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b);">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="kpi-value" style="color:#fbbf24;"><?= number_format($pendingCases ?? 0) ?></div>
            <div class="kpi-label">Pending Cases</div>
            <a href="<?= APP_URL ?>/violations?status=pending" class="kpi-link" id="kpiLinkPending">Review <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:linear-gradient(135deg,#059669,#10b981);">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="kpi-value" style="color:#34d399;"><?= number_format($resolvedCases ?? 0) ?></div>
            <div class="kpi-label">Resolved / Closed</div>
            <a href="<?= APP_URL ?>/violations?status=resolved" class="kpi-link" id="kpiLinkResolved">Browse <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:linear-gradient(135deg,#0891b2,#06b6d4);">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="kpi-value" style="color:#67e8f9;"><?= number_format($totalUsers) ?></div>
            <div class="kpi-label">Registered Users</div>
            <a href="<?= APP_URL ?>/admin/users" class="kpi-link" id="kpiLinkUsers">Manage <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>

</div>

<!-- ── Secondary KPI row ─────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <div class="col-sm-6 col-lg-3">
        <div class="kpi-card-sm">
            <i class="bi bi-search" style="color:#818cf8;"></i>
            <div>
                <div class="kpi-sm-val"><?= number_format($underReview ?? 0) ?></div>
                <div class="kpi-sm-label">Under Review</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="kpi-card-sm">
            <i class="bi bi-tag-fill" style="color:#fbbf24;"></i>
            <div>
                <div class="kpi-sm-val" style="font-size:.95rem;"><?= htmlspecialchars($mostCommonCat ?? '—') ?></div>
                <div class="kpi-sm-label">Top Category</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="kpi-card-sm">
            <i class="bi bi-mortarboard-fill" style="color:#34d399;"></i>
            <div>
                <div class="kpi-sm-val"><?= number_format($totalStudents) ?></div>
                <div class="kpi-sm-label">Students</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="kpi-card-sm">
            <i class="bi bi-person-video2" style="color:#f97316;"></i>
            <div>
                <div class="kpi-sm-val"><?= number_format($totalTeachers) ?></div>
                <div class="kpi-sm-label">Teachers</div>
            </div>
        </div>
    </div>

</div>

<!-- ── Charts row ────────────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <!-- Status donut -->
    <div class="col-md-6 col-xl-3">
        <div class="chart-card">
            <div class="chart-card-title"><i class="bi bi-pie-chart-fill me-2"></i>Status Distribution</div>
            <div style="position:relative;height:200px;">
                <canvas id="chartStatus"></canvas>
            </div>
        </div>
    </div>

    <!-- Category bar -->
    <div class="col-md-6 col-xl-5">
        <div class="chart-card">
            <div class="chart-card-title"><i class="bi bi-bar-chart-fill me-2"></i>Violations by Category</div>
            <div style="position:relative;height:200px;">
                <canvas id="chartCategory"></canvas>
            </div>
        </div>
    </div>

    <!-- Severity donut -->
    <div class="col-md-6 col-xl-4">
        <div class="chart-card">
            <div class="chart-card-title"><i class="bi bi-exclamation-diamond-fill me-2"></i>Severity Breakdown</div>
            <div style="position:relative;height:200px;">
                <canvas id="chartSeverity"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- ── Monthly trend (full width) ────────────────────────────────────────────── -->
<div class="chart-card mb-4">
    <div class="chart-card-title"><i class="bi bi-graph-up me-2"></i>Monthly Violation Trend (Last 6 Months)</div>
    <div style="position:relative;height:220px;">
        <canvas id="chartMonthly"></canvas>
    </div>
</div>

<!-- ── Bottom row: Repeat offenders + Recent violations ───────────────────────── -->
<div class="row g-3 mb-4">

    <!-- Repeat offenders -->
    <div class="col-lg-5">
        <div class="dash-table-card">
            <div class="dash-table-header">
                <span><i class="bi bi-person-exclamation me-2"></i>Repeat Offenders</span>
            </div>
            <?php if (empty($repeatOffenders)): ?>
            <div class="dash-empty"><i class="bi bi-check-circle"></i><p>No repeat offenders found.</p></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>ID No.</th>
                            <th class="text-center">Cases</th>
                            <th>Last</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($repeatOffenders as $off): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($off['student_name']) ?></strong></td>
                            <td><code><?= htmlspecialchars($off['student_number'] ?? '—') ?></code></td>
                            <td class="text-center">
                                <span class="badge-count"><?= $off['violation_count'] ?></span>
                            </td>
                            <td class="text-muted-sm"><?= _admin_rel($off['latest_violation']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent violations -->
    <div class="col-lg-7">
        <div class="dash-table-card">
            <div class="dash-table-header">
                <span><i class="bi bi-clock-history me-2"></i>Recent Violations</span>
                <a href="<?= APP_URL ?>/violations" class="dash-table-link" id="linkAllViolations">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <?php if (empty($recentViolations)): ?>
            <div class="dash-empty"><i class="bi bi-inbox"></i><p>No violations recorded yet.</p></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Filed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentViolations as $v): ?>
                        <tr onclick="location.href='<?= APP_URL ?>/violations/<?= $v['id'] ?>'" style="cursor:pointer;">
                            <td><code>#<?= $v['id'] ?></code></td>
                            <td><?= htmlspecialchars($v['student_name']) ?></td>
                            <td><?= htmlspecialchars($v['type']) ?></td>
                            <td><span class="sev-badge sev-<?= $v['severity'] ?>"><?= ucfirst($v['severity']) ?></span></td>
                            <td><span class="status-badge status-<?= $v['status'] ?>"><?= ucfirst(str_replace('_',' ',$v['status'])) ?></span></td>
                            <td class="text-muted-sm"><?= _admin_rel($v['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ── Recent Notifications ────────────────────────────────────────────────────── -->
<?php include __DIR__ . '/_notifications_widget.php'; ?>

<!-- ── Shared dashboard CSS ───────────────────────────────────────────────────── -->
<?php include __DIR__ . '/_dashboard_styles.php'; ?>

<!-- ── Chart.js ───────────────────────────────────────────────────────────────── -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#94a3b8';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';

const donutOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } }
    },
    cutout: '62%'
};

// Status donut
new Chart(document.getElementById('chartStatus'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($_statusLabels) ?>,
        datasets: [{ data: <?= json_encode($_statusData) ?>, backgroundColor: <?= json_encode($_statusBg) ?>, borderWidth: 2, borderColor: '#0f172a' }]
    },
    options: donutOpts
});

// Category bar
new Chart(document.getElementById('chartCategory'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($_catLabels) ?>,
        datasets: [{
            label: 'Violations',
            data: <?= json_encode($_catData) ?>,
            backgroundColor: <?= json_encode(array_slice($_catBg, 0, count($_catLabels))) ?>,
            borderRadius: 6,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { font: { size: 10 } }, grid: { display: false } },
            y: { ticks: { stepSize: 1 }, beginAtZero: true }
        }
    }
});

// Severity donut
new Chart(document.getElementById('chartSeverity'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($_sevLabels) ?>,
        datasets: [{ data: <?= json_encode($_sevData) ?>, backgroundColor: <?= json_encode($_sevBg) ?>, borderWidth: 2, borderColor: '#0f172a' }]
    },
    options: donutOpts
});

// Monthly trend line
new Chart(document.getElementById('chartMonthly'), {
    type: 'line',
    data: {
        labels: <?= json_encode($_monthLabels) ?>,
        datasets: [{
            label: 'Violations',
            data: <?= json_encode($_monthData) ?>,
            borderColor: '#818cf8',
            backgroundColor: 'rgba(129,140,248,0.12)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#818cf8',
            pointRadius: 5
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
</script>
