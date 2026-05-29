<?php
/**
 * Teacher Dashboard View — Phase 13
 */

$stats = $stats ?? [];
$total       = (int)($stats['total']        ?? 0);
$pending     = (int)($stats['pending']       ?? 0);
$underReview = (int)($stats['under_review']  ?? 0);
$resolved    = (int)($stats['resolved']      ?? 0);
$rejected    = (int)($stats['rejected']      ?? 0);
$closed      = (int)($stats['closed']        ?? 0);

function _teacher_rel(string $ts): string {
    $d = time() - strtotime($ts);
    if ($d < 60)    return 'Just now';
    if ($d < 3600)  return floor($d/60).'m ago';
    if ($d < 86400) return floor($d/3600).'h ago';
    return date('M j', strtotime($ts));
}

// Category chart data
$_catLabels = [];
$_catData   = [];
$_catBg     = ['#818cf8','#34d399','#fbbf24','#f87171','#f97316','#06b6d4','#a78bfa','#94a3b8'];
foreach (($categoryDist ?? []) as $row) {
    $_catLabels[] = $row['category'];
    $_catData[]   = (int) $row['total'];
}
?>

<!-- ── Welcome banner ───────────────────────────────────────────────────────── -->
<div class="dash-banner">
    <div class="dash-banner-icon" style="background:linear-gradient(135deg,#0891b2,#06b6d4);">
        <i class="bi bi-person-video2"></i>
    </div>
    <div>
        <div class="dash-banner-title">Welcome back, <?= htmlspecialchars($user['name'] ?? 'Teacher') ?></div>
        <div class="dash-banner-sub">You have <strong><?= $pending ?></strong> pending report<?= $pending !== 1 ? 's' : '' ?> awaiting admin review.</div>
    </div>
    <div class="dash-banner-actions ms-auto d-flex gap-2 flex-wrap">
        <a href="<?= APP_URL ?>/violations/create" class="btn-dash-action btn-primary-dash" id="btnTeacherFile">
            <i class="bi bi-plus-circle"></i> File Violation
        </a>
        <a href="<?= APP_URL ?>/violations" class="btn-dash-action btn-neutral-dash" id="btnTeacherMyReports">
            <i class="bi bi-list-ul"></i> My Reports
        </a>
    </div>
</div>

<!-- ── KPI cards ─────────────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <div class="col-6 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                <i class="bi bi-file-earmark-text-fill"></i>
            </div>
            <div class="kpi-value"><?= $total ?></div>
            <div class="kpi-label">Total Filed</div>
        </div>
    </div>

    <div class="col-6 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b);">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="kpi-value" style="color:#fbbf24;"><?= $pending ?></div>
            <div class="kpi-label">Pending</div>
        </div>
    </div>

    <div class="col-6 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:linear-gradient(135deg,#4338ca,#818cf8);">
                <i class="bi bi-search"></i>
            </div>
            <div class="kpi-value" style="color:#818cf8;"><?= $underReview ?></div>
            <div class="kpi-label">Under Review</div>
        </div>
    </div>

    <div class="col-6 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:linear-gradient(135deg,#059669,#10b981);">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="kpi-value" style="color:#34d399;"><?= $resolved ?></div>
            <div class="kpi-label">Resolved</div>
        </div>
    </div>

    <div class="col-6 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:linear-gradient(135deg,#b91c1c,#f87171);">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <div class="kpi-value" style="color:#f87171;"><?= $rejected ?></div>
            <div class="kpi-label">Rejected</div>
        </div>
    </div>

    <div class="col-6 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:linear-gradient(135deg,#475569,#94a3b8);">
                <i class="bi bi-lock-fill"></i>
            </div>
            <div class="kpi-value" style="color:#94a3b8;"><?= $closed ?></div>
            <div class="kpi-label">Closed</div>
        </div>
    </div>

</div>

<!-- ── Chart + Recent table ───────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <!-- Category chart -->
    <div class="col-lg-4">
        <div class="chart-card h-100">
            <div class="chart-card-title"><i class="bi bi-pie-chart-fill me-2"></i>My Reports by Category</div>
            <?php if (empty($_catData)): ?>
            <div class="dash-empty"><i class="bi bi-bar-chart"></i><p>No data yet.</p></div>
            <?php else: ?>
            <div style="position:relative;height:220px;">
                <canvas id="chartCatTeacher"></canvas>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent reports -->
    <div class="col-lg-8">
        <div class="dash-table-card h-100">
            <div class="dash-table-header">
                <span><i class="bi bi-clock-history me-2"></i>My Recent Reports</span>
                <a href="<?= APP_URL ?>/violations" class="dash-table-link" id="linkTeacherAllReports">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <?php if (empty($recentViolations)): ?>
            <div class="dash-empty"><i class="bi bi-inbox"></i><p>You haven't filed any reports yet.</p></div>
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
                            <td class="text-muted-sm"><?= _teacher_rel($v['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ── Recent Notifications ──────────────────────────────────────────────────── -->
<?php include __DIR__ . '/_notifications_widget.php'; ?>

<!-- ── Shared dashboard CSS ──────────────────────────────────────────────────── -->
<?php include __DIR__ . '/_dashboard_styles.php'; ?>

<?php if (!empty($_catData)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#94a3b8';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
new Chart(document.getElementById('chartCatTeacher'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($_catLabels) ?>,
        datasets: [{ data: <?= json_encode($_catData) ?>, backgroundColor: <?= json_encode(array_slice($_catBg, 0, count($_catLabels))) ?>, borderWidth: 2, borderColor: '#0f172a' }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '60%',
        plugins: { legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 } } } }
    }
});
</script>
<?php endif; ?>
