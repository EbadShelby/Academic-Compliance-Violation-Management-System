<?php
/**
 * views/violations/show.php
 * Violation detail page
 */
$authUser = Session::user();

// Severity display helpers
$sevClass = [
    'minor'    => 'sev-minor',
    'moderate' => 'sev-moderate',
    'major'    => 'sev-major',
    'critical' => 'sev-critical',
];
$statusClass = [
    'pending'      => 'status-pending',
    'open'         => 'status-pending',
    'under_review' => 'status-under_review',
    'resolved'     => 'status-resolved',
    'rejected'     => 'status-rejected',
    'dismissed'    => 'status-rejected',
    'closed'       => 'status-closed',
];
$statusLabel = [
    'pending'      => 'Pending',
    'open'         => 'Pending',
    'under_review' => 'Under Review',
    'resolved'     => 'Resolved',
    'rejected'     => 'Rejected',
    'dismissed'    => 'Rejected',
    'closed'       => 'Closed',
];

// MIME type → icon
function evidenceIcon(string $mime): string {
    if (str_starts_with($mime, 'image/'))       return 'bi-file-image text-info';
    if ($mime === 'application/pdf')             return 'bi-file-pdf text-danger';
    if (str_contains($mime, 'word'))             return 'bi-file-word text-primary';
    if (str_starts_with($mime, 'text/'))         return 'bi-file-text text-secondary';
    return 'bi-file-earmark';
}

function formatBytes(int $bytes): string {
    if ($bytes < 1024)     return $bytes . ' B';
    if ($bytes < 1048576)  return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}
?>

<style>
/* ── Show page ──────────────────────────────────────────────────────────── */
.detail-card {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: 1rem;
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.detail-card-header {
    display: flex;
    align-items: center;
    gap: .625rem;
    padding: .875rem 1.25rem;
    border-bottom: 1px solid var(--border-subtle);
    font-size: .9375rem;
    font-weight: 700;
    background: rgba(255,255,255,.025);
}
.detail-card-header i { color: var(--brand-primary); }
.detail-card-body { padding: 1.25rem; }

.meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1rem;
}
.meta-item {}
.meta-label {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--text-muted);
    margin-bottom: .25rem;
}
.meta-value { font-size: .9375rem; font-weight: 500; color: var(--text-primary); }

/* Badges */
.badge-sev, .badge-status {
    display: inline-block;
    padding: .25rem .7rem;
    border-radius: .4rem;
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
}
.sev-minor    { background: rgba(52,211,153,.15);  color: #34d399; }
.sev-moderate { background: rgba(251,191,36,.15);  color: #fbbf24; }
.sev-major    { background: rgba(249,115,22,.15);  color: #f97316; }
.sev-critical { background: rgba(248,113,113,.15); color: #f87171; }

.status-pending       { background: rgba(99,102,241,.15);  color: #a5b4fc; }
.status-open          { background: rgba(99,102,241,.15);  color: #a5b4fc; }
.status-under_review  { background: rgba(251,191,36,.15);  color: #fbbf24; }
.status-resolved      { background: rgba(52,211,153,.15);  color: #34d399; }
.status-rejected      { background: rgba(248,113,113,.15); color: #f87171; }
.status-dismissed     { background: rgba(248,113,113,.15); color: #f87171; }
.status-closed        { background: rgba(148,163,184,.1);  color: #94a3b8; }

/* Description block */
.description-block {
    background: rgba(255,255,255,.03);
    border: 1px solid var(--border-subtle);
    border-radius: .75rem;
    padding: 1rem 1.25rem;
    font-size: .9375rem;
    line-height: 1.7;
    color: var(--text-primary);
    white-space: pre-wrap;
    word-break: break-word;
}

/* Evidence files */
.evidence-item {
    display: flex;
    align-items: center;
    gap: .875rem;
    padding: .75rem 1rem;
    border: 1px solid var(--border-subtle);
    border-radius: .75rem;
    background: rgba(255,255,255,.03);
    margin-bottom: .625rem;
    transition: background .12s;
}
.evidence-item:hover { background: rgba(255,255,255,.06); }
.evidence-item:last-child { margin-bottom: 0; }
.evidence-icon { font-size: 1.75rem; flex-shrink: 0; }
.evidence-info { flex: 1; min-width: 0; }
.evidence-name { font-weight: 600; font-size: .9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.evidence-meta { font-size: .775rem; color: var(--text-muted); }

/* Back button */
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .5rem 1rem;
    background: rgba(255,255,255,.06);
    border: 1px solid var(--border-subtle);
    border-radius: .625rem;
    color: var(--text-muted);
    font-size: .875rem;
    text-decoration: none;
    transition: background .15s, color .15s;
}
.btn-back:hover { background: rgba(255,255,255,.1); color: var(--text-primary); }
</style>

<!-- ── Page header ──────────────────────────────────────────────────────── -->
<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <a href="<?= APP_URL ?>/violations" class="btn-back">
        <i class="bi bi-arrow-left"></i> All Violations
    </a>
    <div class="ms-auto d-flex align-items-center gap-2">
        <span class="badge-status <?= $statusClass[$violation['status']] ?? 'status-open' ?>">
            <?= htmlspecialchars($statusLabel[$violation['status']] ?? $violation['status']) ?>
        </span>
        <span class="badge-sev <?= $sevClass[$violation['severity']] ?? '' ?>">
            <?= htmlspecialchars($violation['severity']) ?>
        </span>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">

        <!-- ── Overview ──────────────────────────────────────────────── -->
        <div class="detail-card">
            <div class="detail-card-header">
                <i class="bi bi-exclamation-octagon-fill"></i>
                Violation #<?= $violation['id'] ?> — <?= htmlspecialchars($violation['type']) ?>
            </div>
            <div class="detail-card-body">
                <div class="meta-grid mb-4">
                    <div class="meta-item">
                        <div class="meta-label">Student</div>
                        <div class="meta-value"><?= htmlspecialchars($violation['student_name']) ?></div>
                        <?php if (!empty($violation['student_number'])): ?>
                        <div style="font-size:.8rem;color:var(--text-muted);"><?= htmlspecialchars($violation['student_number']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Reporter</div>
                        <div class="meta-value"><?= htmlspecialchars($violation['reporter_name']) ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Incident Date</div>
                        <div class="meta-value"><?= htmlspecialchars($violation['incident_date']) ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Filed On</div>
                        <div class="meta-value"><?= date('M d, Y', strtotime($violation['created_at'])) ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Category</div>
                        <div class="meta-value"><?= htmlspecialchars($violation['type']) ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Severity</div>
                        <div class="meta-value">
                            <span class="badge-sev <?= $sevClass[$violation['severity']] ?? '' ?>">
                                <?= htmlspecialchars($violation['severity']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="meta-label mb-2">Description</div>
                <div class="description-block<?= !empty($violation['sanction_notes']) ? ' mb-4' : '' ?>"><?= htmlspecialchars($violation['description']) ?></div>

                <?php if (!empty($violation['sanction_notes'])): ?>
                <div class="meta-label mb-2 text-primary"><i class="bi bi-shield-exclamation me-1"></i> Disciplinary Sanction</div>
                <div class="description-block" style="border-color: rgba(167, 139, 250, 0.4); background: rgba(167, 139, 250, 0.05); color: #c4b5fd;">
                    <?= htmlspecialchars($violation['sanction_notes']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Evidence Files ─────────────────────────────────────────── -->
        <div class="detail-card">
            <div class="detail-card-header">
                <i class="bi bi-paperclip"></i>
                Evidence Files
                <span class="ms-auto text-muted" style="font-size:.8rem;font-weight:400;">
                    <?= count($evidenceFiles) ?> file<?= count($evidenceFiles) !== 1 ? 's' : '' ?>
                </span>
            </div>
            <div class="detail-card-body">
                <?php $uploadErrors = Session::getFlash('errors') ?? []; ?>
                <?php if (!empty($uploadErrors)): ?>
                <div class="evidence-alert-err mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <ul class="mb-0 ps-3" style="font-size:.82rem;">
                        <?php foreach ($uploadErrors as $msg): ?>
                        <li><?= htmlspecialchars($msg) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (empty($evidenceFiles)): ?>
                <p class="text-muted mb-0" style="font-size:.875rem;">
                    <i class="bi bi-info-circle me-1"></i> No evidence files have been attached yet.
                </p>
                <?php else: ?>
                    <?php foreach ($evidenceFiles as $ef): ?>
                    <div class="evidence-item">
                        <i class="bi <?= evidenceIcon($ef['mime_type'] ?? '') ?> evidence-icon"></i>
                        <div class="evidence-info">
                            <div class="evidence-name"><?= htmlspecialchars($ef['file_name']) ?></div>
                            <div class="evidence-meta">
                                <?= htmlspecialchars($ef['mime_type'] ?? 'Unknown type') ?>
                                <?php if ($ef['file_size']): ?> · <?= formatBytes((int)$ef['file_size']) ?><?php endif; ?>
                                · <?= htmlspecialchars($ef['uploader_name'] ?? '—') ?>
                                · <?= date('M d, Y', strtotime($ef['created_at'])) ?>
                            </div>
                        </div>
                        <div class="evidence-actions">
                            <a href="<?= APP_URL ?>/evidence/<?= $ef['id'] ?>"
                               class="ev-btn ev-btn-view"
                               target="_blank"
                               title="View / Download">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if (isAdmin() || isRegistrar()): ?>
                            <form method="POST"
                                  action="<?= APP_URL ?>/evidence/<?= $ef['id'] ?>/delete"
                                  style="display:inline;"
                                  onsubmit="return confirm('Permanently delete this evidence file?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="ev-btn ev-btn-delete" title="Delete">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /col-lg-8 -->

    <div class="col-lg-4">

        <!-- ── Actions panel ──────────────────────────────────────────── -->
        <?php if (isAdmin() || isTeacher() || isRegistrar()): ?>
        <div class="detail-card mb-4">
            <div class="detail-card-header">
                <i class="bi bi-lightning-fill"></i>
                Actions
            </div>
            <div class="detail-card-body d-flex flex-column gap-2">
                <?php if (isAdmin() || isRegistrar()): ?>
                <a href="<?= APP_URL ?>/violations/<?= $violation['id'] ?>/review"
                   class="action-btn action-review">
                    <i class="bi bi-clipboard2-check-fill"></i> Review &amp; Manage Case
                </a>
                <?php endif; ?>
                <a href="<?= APP_URL ?>/violations/<?= $violation['id'] ?>/edit"
                   class="action-btn action-edit">
                    <i class="bi bi-pencil-square"></i> Edit Report
                </a>
                <a href="<?= APP_URL ?>/violations/create"
                   class="action-btn action-new">
                    <i class="bi bi-plus-circle"></i> File New Violation
                </a>
            </div>
        </div>

        <!-- ── Add Evidence upload panel ──────────────────────────── -->
        <div class="detail-card mb-4">
            <div class="detail-card-header">
                <i class="bi bi-cloud-arrow-up-fill"></i>
                Add Evidence
            </div>
            <div class="detail-card-body">
                <p class="text-muted mb-3" style="font-size:.8rem;">
                    Attach additional evidence to this violation report.<br>
                    <strong>Allowed:</strong> JPG, PNG, PDF &mdash; max 5 MB each.
                </p>
                <form action="<?= APP_URL ?>/violations/<?= $violation['id'] ?>/evidence"
                      method="POST"
                      enctype="multipart/form-data"
                      id="evidenceUploadForm">
                    <?= csrf_field() ?>

                    <div class="upload-mini-zone" id="miniUploadZone">
                        <input type="file"
                               name="evidence[]"
                               id="miniEvidenceInput"
                               class="upload-mini-input"
                               multiple
                               accept=".jpg,.jpeg,.png,.pdf">
                        <i class="bi bi-paperclip" style="font-size:1.5rem;color:var(--text-muted);"></i>
                        <span style="font-size:.8125rem;color:var(--text-muted);">
                            Drop files here or <label for="miniEvidenceInput" class="upload-mini-label">browse</label>
                        </span>
                    </div>

                    <div id="miniFileList" class="mini-file-list mt-2"></div>

                    <button type="submit"
                            class="action-btn action-upload w-100 justify-content-center mt-3"
                            id="evidenceSubmitBtn">
                        <i class="bi bi-upload"></i> Upload Evidence
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($authUser['role'] === 'student' && $violation['status'] !== 'closed'): ?>
        <!-- ── Student Appeal Form ────────────────────────────────────────── -->
        <div class="detail-card mb-4" style="border-color: rgba(99, 102, 241, 0.4);">
            <div class="detail-card-header" style="background: rgba(99, 102, 241, 0.05);">
                <i class="bi bi-chat-left-text-fill" style="color: #818cf8;"></i>
                Submit Defense / Appeal
            </div>
            <div class="detail-card-body">
                <p class="text-muted mb-3" style="font-size:.8rem; line-height: 1.5;">
                    If you wish to provide context, defend yourself, or formally appeal this case, you may submit a statement below. Administrators will review your submission.
                </p>
                <form action="<?= APP_URL ?>/violations/<?= $violation['id'] ?>/appeal" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <textarea class="form-control bg-dark text-light border-secondary"
                                  name="appeal_reason"
                                  rows="4"
                                  placeholder="Enter your defense or appeal reason here..."
                                  required></textarea>
                    </div>
                    <button type="submit" class="action-btn w-100 justify-content-center" style="background: rgba(99,102,241,.15); color: #a5b4fc; border: 1px solid rgba(99,102,241,.3);">
                        <i class="bi bi-send-fill"></i> Submit Statement
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($authUser['role'] === 'student' && !empty($actions)): ?>
            <?php 
                $hasAppeal = false;
                foreach ($actions as $act) {
                    if ($act['action_type'] === 'student_appeal') {
                        $hasAppeal = true;
                        break;
                    }
                }
            ?>
            <?php if ($hasAppeal): ?>
            <!-- ── Student Appeal History ────────────────────────────────────── -->
            <div class="detail-card mb-4">
                <div class="detail-card-header">
                    <i class="bi bi-clock-history"></i>
                    Your Submissions
                </div>
                <div class="detail-card-body p-3">
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($actions as $act): ?>
                            <?php if ($act['action_type'] === 'student_appeal'): ?>
                            <div class="p-3 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-subtle);">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge" style="background: rgba(99,102,241,.2); color: #a5b4fc; font-weight: 600;">Appeal / Defense</span>
                                    <small class="text-muted"><?= date('M d, Y H:i', strtotime($act['created_at'])) ?></small>
                                </div>
                                <div style="font-size: .875rem; color: var(--text-primary); white-space: pre-wrap;"><?= htmlspecialchars(str_replace('Student Defense/Appeal: ', '', $act['note'] ?? '')) ?></div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- ── Audit trail note ────────────────────────────────────────── -->
        <div class="detail-card">
            <div class="detail-card-header">
                <i class="bi bi-shield-lock-fill"></i>
                Audit Information
            </div>
            <div class="detail-card-body">
                <p class="text-muted mb-0" style="font-size:.8125rem;line-height:1.6;">
                    This violation report is permanently logged in the audit trail.
                    All changes are tracked and attributed to the acting user.
                </p>
                <hr style="border-color:var(--border-subtle);">
                <div style="font-size:.8rem;color:var(--text-muted);">
                    <div class="mb-1"><strong>Record ID:</strong> #<?= $violation['id'] ?></div>
                    <div class="mb-1"><strong>Created:</strong> <?= date('M d, Y H:i', strtotime($violation['created_at'])) ?></div>
                    <div><strong>Updated:</strong> <?= date('M d, Y H:i', strtotime($violation['updated_at'])) ?></div>
                </div>
            </div>
        </div>

    </div><!-- /col-lg-4 -->
</div>

<style>
.action-btn {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .625rem 1rem;
    border-radius: .625rem;
    font-size: .875rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    transition: opacity .15s, transform .1s;
}
.action-btn:hover { opacity: .88; transform: translateY(-1px); }
.action-review { background: rgba(251,191,36,.12);  color: #fbbf24; border-color: rgba(251,191,36,.3); }
.action-edit   { background: rgba(79,70,229,.15);   color: #a5b4fc; border-color: rgba(79,70,229,.3); }
.action-new    { background: rgba(52,211,153,.1);   color: #34d399; border-color: rgba(52,211,153,.25); }
.action-upload { background: rgba(6,182,212,.12);   color: #22d3ee; border-color: rgba(6,182,212,.3);  }

/* Evidence item actions */
.evidence-item { align-items: flex-start; }
.evidence-actions {
    display: flex;
    align-items: center;
    gap: .375rem;
    flex-shrink: 0;
    margin-top: .1rem;
}
.ev-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px; height: 30px;
    border-radius: .4rem;
    border: 1px solid var(--border-subtle);
    background: rgba(255,255,255,.05);
    color: var(--text-muted);
    font-size: .9rem;
    text-decoration: none;
    cursor: pointer;
    transition: background .12s, color .12s;
}
.ev-btn-view:hover   { background: rgba(99,102,241,.2);  color: #a5b4fc; }
.ev-btn-delete:hover { background: rgba(248,113,113,.2); color: #f87171; }

/* Evidence error alert */
.evidence-alert-err {
    display: flex;
    align-items: flex-start;
    gap: .6rem;
    padding: .75rem 1rem;
    border-radius: .625rem;
    background: rgba(248,113,113,.1);
    border: 1px solid rgba(248,113,113,.3);
    color: #f87171;
    font-size: .875rem;
}
.evidence-alert-err > i { flex-shrink: 0; margin-top: .1rem; }

/* Mini upload zone */
.upload-mini-zone {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .4rem;
    padding: 1rem;
    border: 2px dashed var(--border-subtle);
    border-radius: .625rem;
    text-align: center;
    cursor: pointer;
    transition: border-color .15s, background .15s;
}
.upload-mini-zone:hover,
.upload-mini-zone.drag-over {
    border-color: var(--brand-accent);
    background: rgba(6,182,212,.05);
}
.upload-mini-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}
.upload-mini-label {
    color: #22d3ee;
    cursor: pointer;
    text-decoration: underline;
}
.mini-file-list { display: flex; flex-direction: column; gap: .35rem; }
.mini-file-item {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .375rem .625rem;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--border-subtle);
    border-radius: .5rem;
    font-size: .8rem;
}
.mini-file-item span { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.mini-file-item .remove-mini {
    background: none; border: none; color: #f87171;
    cursor: pointer; padding: 0 .15rem; font-size: .9rem;
}
</style>

<script>
(function () {
    // Mini upload zone file preview
    const miniInput = document.getElementById('miniEvidenceInput');
    const miniList  = document.getElementById('miniFileList');
    const miniZone  = document.getElementById('miniUploadZone');
    const submitBtn = document.getElementById('evidenceSubmitBtn');
    let   miniFiles = [];

    function escH(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    function fmtSz(b) {
        if (b < 1024) return b + ' B';
        if (b < 1048576) return (b/1024).toFixed(1) + ' KB';
        return (b/1048576).toFixed(1) + ' MB';
    }

    function renderMiniList() {
        if (!miniList) return;
        miniList.innerHTML = '';
        miniFiles.forEach((f, i) => {
            const el = document.createElement('div');
            el.className = 'mini-file-item';
            el.innerHTML = `<i class="bi bi-file-earmark" style="flex-shrink:0;"></i>
                <span>${escH(f.name)}</span>
                <small class="text-muted">${fmtSz(f.size)}</small>
                <button type="button" class="remove-mini" data-i="${i}">
                    <i class="bi bi-x"></i>
                </button>`;
            miniList.appendChild(el);
        });
        miniList.querySelectorAll('.remove-mini').forEach(btn => {
            btn.addEventListener('click', function() {
                miniFiles.splice(parseInt(this.dataset.i), 1);
                syncMini(); renderMiniList();
            });
        });
    }

    function syncMini() {
        if (!miniInput) return;
        const dt = new DataTransfer();
        miniFiles.forEach(f => dt.items.add(f));
        miniInput.files = dt.files;
    }

    miniInput && miniInput.addEventListener('change', function() {
        Array.from(this.files).forEach(f => miniFiles.push(f));
        syncMini(); renderMiniList();
    });

    miniZone && miniZone.addEventListener('dragover', e => { e.preventDefault(); miniZone.classList.add('drag-over'); });
    miniZone && miniZone.addEventListener('dragleave', () => miniZone.classList.remove('drag-over'));
    miniZone && miniZone.addEventListener('drop', e => {
        e.preventDefault(); miniZone.classList.remove('drag-over');
        Array.from(e.dataTransfer.files).forEach(f => miniFiles.push(f));
        syncMini(); renderMiniList();
    });

    const evidenceForm = document.getElementById('evidenceUploadForm');
    evidenceForm && evidenceForm.addEventListener('submit', function() {
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Uploading…';
        }
    });
})();
</script>
