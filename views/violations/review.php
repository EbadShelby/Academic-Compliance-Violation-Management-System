<?php
/**
 * views/violations/review.php
 * Admin-only case management / review page — Phase 10
 */
$authUser = Session::user();

$sevClass = [
    'minor'    => 'sev-minor',
    'moderate' => 'sev-moderate',
    'major'    => 'sev-major',
    'critical' => 'sev-critical',
];
$statusClass = [
    'pending'      => 'status-pending',
    'under_review' => 'status-under_review',
    'resolved'     => 'status-resolved',
    'rejected'     => 'status-rejected',
    'closed'       => 'status-closed',
];

$isClosed = ($violation['status'] === 'closed');

function reviewEvidenceIcon(string $mime): string {
    if (str_starts_with($mime, 'image/'))   return 'bi-file-image text-info';
    if ($mime === 'application/pdf')         return 'bi-file-pdf text-danger';
    return 'bi-file-earmark';
}
function reviewFmtBytes(int $bytes): string {
    if ($bytes < 1024)    return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}

$actionIcons = [
    'case_filed'        => 'bi-file-earmark-plus text-info',
    'status_changed'    => 'bi-arrow-left-right text-warning',
    'case_rejected'     => 'bi-x-circle text-danger',
    'sanction_assigned' => 'bi-shield-exclamation text-purple',
    'case_closed'       => 'bi-lock-fill text-secondary',
];
?>

<style>
/* ── Review page layout ─────────────────────────────────────────────────── */
.review-card {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: 1rem;
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.review-card-header {
    display: flex;
    align-items: center;
    gap: .625rem;
    padding: .875rem 1.25rem;
    border-bottom: 1px solid var(--border-subtle);
    font-size: .9375rem;
    font-weight: 700;
    background: rgba(255,255,255,.025);
}
.review-card-header i { color: var(--brand-primary); }
.review-card-body { padding: 1.25rem; }

/* Meta grid */
.meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px,1fr));
    gap: 1rem;
}
.meta-label {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--text-muted);
    margin-bottom: .2rem;
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
.sev-minor    { background:rgba(52,211,153,.15);  color:#34d399; }
.sev-moderate { background:rgba(251,191,36,.15);  color:#fbbf24; }
.sev-major    { background:rgba(249,115,22,.15);  color:#f97316; }
.sev-critical { background:rgba(248,113,113,.15); color:#f87171; }

.status-pending      { background:rgba(99,102,241,.15);  color:#a5b4fc; }
.status-under_review { background:rgba(251,191,36,.15);  color:#fbbf24; }
.status-resolved     { background:rgba(52,211,153,.15);  color:#34d399; }
.status-rejected     { background:rgba(248,113,113,.15); color:#f87171; }
.status-closed       { background:rgba(148,163,184,.1);  color:#94a3b8; }

/* Description */
.desc-block {
    background: rgba(255,255,255,.03);
    border: 1px solid var(--border-subtle);
    border-radius: .75rem;
    padding: 1rem 1.25rem;
    font-size: .9375rem;
    line-height: 1.7;
    white-space: pre-wrap;
    word-break: break-word;
}

/* Workflow buttons */
.wf-btn {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .6rem 1rem;
    border-radius: .625rem;
    font-size: .875rem;
    font-weight: 600;
    border: 1px solid transparent;
    cursor: pointer;
    transition: opacity .15s, transform .1s;
    text-decoration: none;
    width: 100%;
    justify-content: center;
    background: none;
}
.wf-btn:hover { opacity: .85; transform: translateY(-1px); }
.wf-review  { background:rgba(251,191,36,.12); color:#fbbf24; border-color:rgba(251,191,36,.3); }
.wf-resolve { background:rgba(52,211,153,.12); color:#34d399; border-color:rgba(52,211,153,.3); }
.wf-close   { background:rgba(148,163,184,.1); color:#94a3b8; border-color:rgba(148,163,184,.25); }
.wf-reject  { background:rgba(248,113,113,.1); color:#f87171; border-color:rgba(248,113,113,.3); }
.wf-disabled{ background:rgba(255,255,255,.03); color:var(--text-muted); border-color:var(--border-subtle); cursor:not-allowed; opacity:.5; }

/* Sanction box */
.sanction-existing {
    background: rgba(139,92,246,.08);
    border: 1px solid rgba(139,92,246,.25);
    border-radius: .75rem;
    padding: .875rem 1rem;
    font-size: .875rem;
    color: #c4b5fd;
    white-space: pre-wrap;
    word-break: break-word;
    margin-bottom: 1rem;
}

/* Rejection reason box */
.rejection-box {
    background: rgba(248,113,113,.07);
    border: 1px solid rgba(248,113,113,.25);
    border-radius: .75rem;
    padding: .875rem 1rem;
    font-size: .875rem;
    color: #f87171;
    white-space: pre-wrap;
    word-break: break-word;
    margin-bottom: 1rem;
}

/* Evidence items */
.ev-item {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .625rem .875rem;
    border: 1px solid var(--border-subtle);
    border-radius: .625rem;
    background: rgba(255,255,255,.02);
    margin-bottom: .5rem;
    transition: background .12s;
}
.ev-item:hover { background: rgba(255,255,255,.05); }
.ev-item:last-child { margin-bottom: 0; }
.ev-icon { font-size: 1.5rem; flex-shrink: 0; }
.ev-info { flex: 1; min-width: 0; }
.ev-name { font-weight: 600; font-size: .875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ev-meta { font-size: .75rem; color: var(--text-muted); }
.ev-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px; height: 28px;
    border-radius: .4rem;
    border: 1px solid var(--border-subtle);
    background: rgba(255,255,255,.04);
    color: var(--text-muted);
    font-size: .85rem;
    text-decoration: none;
    transition: background .12s, color .12s;
}
.ev-link:hover { background: rgba(99,102,241,.2); color: #a5b4fc; }

/* Action timeline */
.timeline { position: relative; padding-left: 2rem; }
.timeline::before {
    content: '';
    position: absolute;
    left: .6rem;
    top: 0; bottom: 0;
    width: 2px;
    background: var(--border-subtle);
}
.tl-item {
    position: relative;
    padding-bottom: 1.25rem;
}
.tl-item:last-child { padding-bottom: 0; }
.tl-dot {
    position: absolute;
    left: -1.575rem;
    top: .15rem;
    width: 1.1rem;
    height: 1.1rem;
    border-radius: 50%;
    background: var(--surface-card);
    border: 2px solid var(--border-subtle);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .55rem;
}
.tl-dot.dot-file    { border-color: #22d3ee; }
.tl-dot.dot-status  { border-color: #fbbf24; }
.tl-dot.dot-reject  { border-color: #f87171; }
.tl-dot.dot-sanction{ border-color: #a78bfa; }
.tl-dot.dot-close   { border-color: #94a3b8; }
.tl-time { font-size: .72rem; color: var(--text-muted); }
.tl-text { font-size: .85rem; color: var(--text-primary); margin-top: .1rem; }
.tl-actor { font-size: .75rem; color: var(--text-muted); }

/* Back btn */
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

/* Closed notice */
.closed-banner {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .875rem 1.125rem;
    background: rgba(148,163,184,.08);
    border: 1px solid rgba(148,163,184,.2);
    border-radius: .75rem;
    color: #94a3b8;
    font-size: .875rem;
    margin-bottom: 1.25rem;
}

/* Form controls */
.rev-textarea {
    width: 100%;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--border-subtle);
    border-radius: .5rem;
    color: var(--text-primary);
    font-size: .875rem;
    padding: .625rem .875rem;
    resize: vertical;
    outline: none;
    font-family: inherit;
    transition: border-color .15s;
}
.rev-textarea:focus { border-color: var(--brand-primary); }
.rev-textarea:disabled { opacity: .5; cursor: not-allowed; }

.text-purple { color: #a78bfa; }

/* AI Case Summary ── Phase 3 */
.ai-summary-card-wrap {
    border: 1px solid rgba(139,92,246,.3);
    border-radius: .75rem;
    background: rgba(139,92,246,.06);
    overflow: hidden;
}
.ai-summary-btn {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .5rem 1.1rem;
    background: linear-gradient(135deg, rgba(139,92,246,.25), rgba(79,70,229,.2));
    border: 1px solid rgba(139,92,246,.4);
    border-radius: .625rem;
    color: #c4b5fd;
    font-size: .875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    width: 100%;
    justify-content: center;
}
.ai-summary-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, rgba(139,92,246,.4), rgba(79,70,229,.35));
    border-color: rgba(139,92,246,.65);
    color: #ddd6fe;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(139,92,246,.2);
}
.ai-summary-btn:disabled { opacity:.6; cursor:not-allowed; }
.ai-summary-result {
    margin-top: .875rem;
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(139,92,246,.2);
    border-radius: .625rem;
    padding: .875rem 1rem;
    font-size: .875rem;
    color: var(--text-primary);
    line-height: 1.7;
    position: relative;
    animation: aiSlideIn .25s ease;
}
.ai-summary-copy-btn {
    position: absolute;
    top: .5rem;
    right: .5rem;
    background: rgba(139,92,246,.15);
    border: 1px solid rgba(139,92,246,.3);
    border-radius: .375rem;
    color: #c4b5fd;
    font-size: .8rem;
    padding: .25rem .6rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: .3rem;
    transition: background .15s;
}
.ai-summary-copy-btn:hover { background: rgba(139,92,246,.3); }
.ai-summary-hint {
    font-size: .74rem;
    color: var(--text-muted);
    margin-top: .625rem;
    font-style: italic;
}
</style>

<!-- ── Page header ─────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <a href="<?= APP_URL ?>/violations/<?= $violation['id'] ?>" class="btn-back">
        <i class="bi bi-arrow-left"></i> View Report
    </a>
    <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
        <span class="badge-status <?= $statusClass[$violation['status']] ?? 'status-pending' ?>">
            <?= htmlspecialchars($statusLabels[$violation['status']] ?? $violation['status']) ?>
        </span>
        <span class="badge-sev sev-<?= htmlspecialchars($violation['severity']) ?>">
            <?= htmlspecialchars($violation['severity']) ?>
        </span>
    </div>
</div>

<!-- Flash messages -->
<?php if ($success = Session::getFlash('success')): ?>
<div class="alert alert-success d-flex align-items-center gap-2 mb-4 rounded-3" role="alert">
    <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>
<?php if ($err = Session::getFlash('error')): ?>
<div class="alert alert-danger d-flex align-items-center gap-2 mb-4 rounded-3" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($err) ?>
</div>
<?php endif; ?>

<?php if ($isClosed): ?>
<div class="closed-banner">
    <i class="bi bi-lock-fill fs-5"></i>
    <span>This case is <strong>closed</strong> and is now read-only. No further changes are permitted.</span>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- ── LEFT COLUMN ──────────────────────────────────────────────────────── -->
    <div class="col-lg-8">

        <!-- Case Overview -->
        <div class="review-card">
            <div class="review-card-header">
                <i class="bi bi-exclamation-octagon-fill"></i>
                Case #<?= $violation['id'] ?> — <?= htmlspecialchars($violation['type']) ?>
            </div>
            <div class="review-card-body">
                <div class="meta-grid mb-4">
                    <div>
                        <div class="meta-label">Student</div>
                        <div class="meta-value"><?= htmlspecialchars($violation['student_name']) ?></div>
                        <?php if (!empty($violation['student_number'])): ?>
                        <div style="font-size:.78rem;color:var(--text-muted);"><?= htmlspecialchars($violation['student_number']) ?></div>
                        <?php endif; ?>
                        <div style="font-size:.78rem;color:var(--text-muted);"><?= htmlspecialchars($violation['student_email'] ?? '') ?></div>
                    </div>
                    <div>
                        <div class="meta-label">Reported By</div>
                        <div class="meta-value"><?= htmlspecialchars($violation['reporter_name']) ?></div>
                        <div style="font-size:.78rem;color:var(--text-muted);"><?= htmlspecialchars($violation['reporter_email'] ?? '') ?></div>
                    </div>
                    <div>
                        <div class="meta-label">Incident Date</div>
                        <div class="meta-value"><?= htmlspecialchars($violation['incident_date']) ?></div>
                    </div>
                    <div>
                        <div class="meta-label">Filed On</div>
                        <div class="meta-value"><?= date('M d, Y', strtotime($violation['created_at'])) ?></div>
                    </div>
                    <div>
                        <div class="meta-label">Category</div>
                        <div class="meta-value"><?= htmlspecialchars($violation['type']) ?></div>
                    </div>
                    <div>
                        <div class="meta-label">Severity</div>
                        <div class="meta-value">
                            <span class="badge-sev sev-<?= htmlspecialchars($violation['severity']) ?>">
                                <?= htmlspecialchars($violation['severity']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="meta-label mb-2">Description</div>
                <div class="desc-block"><?= htmlspecialchars($violation['description']) ?></div>
            </div>
        </div>

        <!-- Sanction Notes (if any) -->
        <?php if (!empty($violation['sanction_notes'])): ?>
        <div class="review-card">
            <div class="review-card-header">
                <i class="bi bi-shield-exclamation text-purple"></i>
                Active Sanction
            </div>
            <div class="review-card-body">
                <div class="sanction-existing"><?= htmlspecialchars($violation['sanction_notes']) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Rejection Reason (if any) -->
        <?php if (!empty($violation['rejection_reason'])): ?>
        <div class="review-card">
            <div class="review-card-header">
                <i class="bi bi-x-circle text-danger"></i>
                Rejection Reason
            </div>
            <div class="review-card-body">
                <div class="rejection-box"><?= htmlspecialchars($violation['rejection_reason']) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Evidence Files -->
        <div class="review-card">
            <div class="review-card-header">
                <i class="bi bi-paperclip"></i>
                Evidence Files
                <span class="ms-auto text-muted" style="font-size:.78rem;font-weight:400;">
                    <?= count($evidenceFiles) ?> file<?= count($evidenceFiles) !== 1 ? 's' : '' ?>
                </span>
            </div>
            <div class="review-card-body">
                <?php if (empty($evidenceFiles)): ?>
                <p class="text-muted mb-0" style="font-size:.875rem;">
                    <i class="bi bi-info-circle me-1"></i> No evidence files attached.
                </p>
                <?php else: ?>
                    <?php foreach ($evidenceFiles as $ef): ?>
                    <div class="ev-item">
                        <i class="bi <?= reviewEvidenceIcon($ef['mime_type'] ?? '') ?> ev-icon"></i>
                        <div class="ev-info">
                            <div class="ev-name"><?= htmlspecialchars($ef['file_name']) ?></div>
                            <div class="ev-meta">
                                <?= htmlspecialchars($ef['mime_type'] ?? 'Unknown') ?>
                                <?php if ($ef['file_size']): ?> · <?= reviewFmtBytes((int)$ef['file_size']) ?><?php endif; ?>
                                · <?= date('M d, Y', strtotime($ef['created_at'])) ?>
                            </div>
                        </div>
                        <a href="<?= APP_URL ?>/evidence/<?= $ef['id'] ?>"
                           class="ev-link" target="_blank" title="View / Download">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action History Timeline -->
        <div class="review-card">
            <div class="review-card-header">
                <i class="bi bi-clock-history"></i>
                Case History
                <span class="ms-auto text-muted" style="font-size:.78rem;font-weight:400;">
                    <?= count($actions) ?> event<?= count($actions) !== 1 ? 's' : '' ?>
                </span>
            </div>
            <div class="review-card-body">
                <?php if (empty($actions)): ?>
                <p class="text-muted mb-0" style="font-size:.875rem;">No actions recorded yet.</p>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach ($actions as $act):
                        $dotClass = match($act['action_type']) {
                            'case_filed'        => 'dot-file',
                            'status_changed'    => 'dot-status',
                            'case_rejected'     => 'dot-reject',
                            'sanction_assigned' => 'dot-sanction',
                            'case_closed'       => 'dot-close',
                            default             => '',
                        };
                    ?>
                    <div class="tl-item">
                        <div class="tl-dot <?= $dotClass ?>"></div>
                        <div class="tl-time"><?= date('M d, Y H:i', strtotime($act['created_at'])) ?></div>
                        <div class="tl-text"><?= htmlspecialchars($act['note'] ?? ucwords(str_replace('_', ' ', $act['action_type']))) ?></div>
                        <div class="tl-actor">by <?= htmlspecialchars($act['actor_name'] ?? '—') ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /col-lg-8 -->

    <!-- ── RIGHT COLUMN ─────────────────────────────────────────────────────── -->
    <div class="col-lg-4">

        <!-- ── Workflow Controls ─────────────────────────────────────────────── -->
        <div class="review-card mb-4">
            <div class="review-card-header">
                <i class="bi bi-diagram-3-fill"></i>
                Workflow
            </div>
            <div class="review-card-body d-flex flex-column gap-2">

                <?php if ($isClosed): ?>
                <button class="wf-btn wf-disabled" disabled>
                    <i class="bi bi-lock-fill"></i> Case Closed — Read Only
                </button>

                <?php else: ?>

                    <!-- Move to Under Review -->
                    <?php if (in_array('under_review', $availableTransitions, true)): ?>
                    <form method="POST" action="<?= APP_URL ?>/violations/<?= $violation['id'] ?>/status">
                        <?= csrf_field() ?>
                        <input type="hidden" name="new_status" value="under_review">
                        <button type="submit" class="wf-btn wf-review">
                            <i class="bi bi-eye-fill"></i> Move to Under Review
                        </button>
                    </form>
                    <?php endif; ?>

                    <!-- Resolve -->
                    <?php if (in_array('resolved', $availableTransitions, true)): ?>
                    <form method="POST" action="<?= APP_URL ?>/violations/<?= $violation['id'] ?>/status">
                        <?= csrf_field() ?>
                        <input type="hidden" name="new_status" value="resolved">
                        <button type="submit" class="wf-btn wf-resolve"
                                onclick="return confirm('Mark this case as Resolved?')">
                            <i class="bi bi-check-circle-fill"></i> Mark as Resolved
                        </button>
                    </form>
                    <?php endif; ?>

                    <!-- Close -->
                    <?php if (in_array('closed', $availableTransitions, true)): ?>
                    <form method="POST" action="<?= APP_URL ?>/violations/<?= $violation['id'] ?>/close">
                        <?= csrf_field() ?>
                        <button type="submit" class="wf-btn wf-close"
                                onclick="return confirm('Close this case permanently? This cannot be undone.')">
                            <i class="bi bi-lock-fill"></i> Close Case
                        </button>
                    </form>
                    <?php endif; ?>

                    <!-- Show current state label if no transitions available (shouldn't happen for non-closed) -->
                    <?php if (empty($availableTransitions) && !$isClosed): ?>
                    <button class="wf-btn wf-disabled" disabled>
                        <i class="bi bi-hourglass"></i> No transitions available
                    </button>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>

        <!-- ── AI Tools ──────────────────────────────── -->
        <div class="review-card mb-4">
            <div class="review-card-header">
                <i class="bi bi-stars" style="color:#a78bfa;"></i>
                AI Tools
            </div>
            <div class="review-card-body">
                <p class="text-muted mb-3" style="font-size:.8125rem;">
                    Generate a concise professional summary of this case for internal records.
                    Student names are never sent to the AI.
                </p>
                <button type="button" id="aiSummaryBtn" class="ai-summary-btn mb-1">
                    <i class="bi bi-stars"></i>
                    <span id="aiSummaryBtnText">Generate AI Case Summary</span>
                </button>
                <div id="aiSummaryLoading" class="ai-loading" style="display:none;">
                    <div class="ai-spinner"></div>
                    <span>Generating summary…</span>
                </div>
                <div id="aiSummaryError" class="ai-error" style="display:none;">
                    <i class="bi bi-exclamation-circle"></i>
                    <span id="aiSummaryErrorText"></span>
                </div>
                <div id="aiSummaryResult" class="ai-summary-result" style="display:none;">
                    <button type="button" id="aiSummaryCopyBtn" class="ai-summary-copy-btn" title="Copy to clipboard">
                        <i class="bi bi-clipboard" id="aiSummaryCopyIcon"></i> Copy
                    </button>
                    <p id="aiSummaryText" style="margin:0;padding-right:4.5rem;"></p>
                </div>
                <p class="ai-summary-hint" id="aiSummaryHint" style="display:none;">
                    <i class="bi bi-info-circle me-1"></i>AI-generated. Review before use.
                </p>
            </div>
        </div>

        <!-- ── Reject Case ───────────────────────────────────────────────────── -->
        <?php if (!$isClosed && in_array('rejected', $availableTransitions, true)): ?>
        <div class="review-card mb-4">
            <div class="review-card-header">
                <i class="bi bi-x-circle text-danger"></i>
                Reject Case
            </div>
            <div class="review-card-body">
                <p class="text-muted mb-3" style="font-size:.8125rem;">
                    Rejection requires a written reason. The student and reporter will see this.
                </p>
                <form method="POST" action="<?= APP_URL ?>/violations/<?= $violation['id'] ?>/reject"
                      id="rejectForm">
                    <?= csrf_field() ?>
                    <textarea name="rejection_reason"
                              id="rejectionReason"
                              class="rev-textarea mb-3"
                              rows="4"
                              placeholder="Enter rejection reason…"
                              required
                              maxlength="1000"
                              <?= $isClosed ? 'disabled' : '' ?>></textarea>
                    <button type="submit"
                            class="wf-btn wf-reject"
                            onclick="return confirm('Are you sure you want to reject this case? This action will be logged.')">
                        <i class="bi bi-x-circle-fill"></i> Confirm Reject
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── Assign Sanction ───────────────────────────────────────────────── -->
        <div class="review-card mb-4">
            <div class="review-card-header">
                <i class="bi bi-shield-exclamation text-purple"></i>
                Assign Sanction
            </div>
            <div class="review-card-body">
                <?php if ($isClosed): ?>
                <p class="text-muted mb-0" style="font-size:.8125rem;">
                    <i class="bi bi-lock me-1"></i> Sanctions cannot be modified on a closed case.
                </p>
                <?php else: ?>
                <p class="text-muted mb-3" style="font-size:.8125rem;">
                    Document the sanction applied (warning, suspension, counselling, etc.).
                </p>
                <form method="POST" action="<?= APP_URL ?>/violations/<?= $violation['id'] ?>/sanction">
                    <?= csrf_field() ?>
                    <textarea name="sanction_notes"
                              id="sanctionNotes"
                              class="rev-textarea mb-3"
                              rows="4"
                              placeholder="Describe the sanction…"
                              maxlength="2000"><?= htmlspecialchars($violation['sanction_notes'] ?? '') ?></textarea>
                    <button type="submit" class="wf-btn" style="background:rgba(139,92,246,.12);color:#a78bfa;border-color:rgba(139,92,246,.3);">
                        <i class="bi bi-shield-check"></i> Save Sanction
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Audit Info ────────────────────────────────────────────────────── -->
        <div class="review-card">
            <div class="review-card-header">
                <i class="bi bi-shield-lock-fill"></i>
                Record Info
            </div>
            <div class="review-card-body" style="font-size:.8rem;color:var(--text-muted);">
                <div class="mb-1"><strong>ID:</strong> #<?= $violation['id'] ?></div>
                <div class="mb-1"><strong>Created:</strong> <?= date('M d, Y H:i', strtotime($violation['created_at'])) ?></div>
                <div class="mb-1"><strong>Updated:</strong> <?= date('M d, Y H:i', strtotime($violation['updated_at'])) ?></div>
                <div><strong>Status:</strong>
                    <span class="badge-status <?= $statusClass[$violation['status']] ?? '' ?>">
                        <?= htmlspecialchars($statusLabels[$violation['status']] ?? $violation['status']) ?>
                    </span>
                </div>
            </div>
        </div>

    </div><!-- /col-lg-4 -->
</div>

<?php
// Pass violation context to JS as safe JSON (no student name — PII guard)
$aiCasePayload = json_encode([
    'type'          => $violation['type']          ?? '',
    'severity'      => $violation['severity']      ?? '',
    'description'   => $violation['description']   ?? '',
    'status'        => $violation['status']        ?? '',
    'incident_date' => $violation['incident_date'] ?? '',
    'sanction_notes'=> $violation['sanction_notes'] ?? '',
    'action_count'  => count($actions),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>

<script>
(function () {
    // ── AI Case Summary (Phase 3) ─────────────────────────────────────────────
    const caseData       = <?= $aiCasePayload ?>;
    const aiSummaryBtn   = document.getElementById('aiSummaryBtn');
    const aiSummaryBtnTxt= document.getElementById('aiSummaryBtnText');
    const aiSummaryLoad  = document.getElementById('aiSummaryLoading');
    const aiSummaryErr   = document.getElementById('aiSummaryError');
    const aiSummaryErrTxt= document.getElementById('aiSummaryErrorText');
    const aiSummaryResult= document.getElementById('aiSummaryResult');
    const aiSummaryText  = document.getElementById('aiSummaryText');
    const aiSummaryHint  = document.getElementById('aiSummaryHint');
    const aiSummaryCopyBtn = document.getElementById('aiSummaryCopyBtn');
    const aiSummaryCopyIcon= document.getElementById('aiSummaryCopyIcon');

    function showSummaryState(state) {
        aiSummaryLoad.style.display   = state === 'loading' ? 'flex'  : 'none';
        aiSummaryErr.style.display    = state === 'error'   ? 'flex'  : 'none';
        aiSummaryResult.style.display = state === 'success' ? 'block' : 'none';
        aiSummaryHint.style.display   = state === 'success' ? 'block' : 'none';
    }

    aiSummaryBtn && aiSummaryBtn.addEventListener('click', async function () {
        aiSummaryBtn.disabled = true;
        aiSummaryBtnTxt.textContent = 'Generating…';
        showSummaryState('loading');

        try {
            const response = await fetch('<?= APP_URL ?>/ai/summarize-case', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(caseData)
            });

            const json = await response.json();

            if (!json.success) {
                showSummaryState('error');
                aiSummaryErrTxt.textContent = json.error || 'AI summary generation failed. Please try again.';
                return;
            }

            aiSummaryText.textContent = json.data.summary;
            showSummaryState('success');

        } catch (err) {
            showSummaryState('error');
            aiSummaryErrTxt.textContent = 'Network error. Please check your connection and try again.';
            console.error('AI summary error:', err);
        } finally {
            aiSummaryBtn.disabled = false;
            aiSummaryBtnTxt.textContent = 'Generate AI Case Summary';
        }
    });

    // Copy to clipboard
    aiSummaryCopyBtn && aiSummaryCopyBtn.addEventListener('click', function () {
        const text = aiSummaryText ? aiSummaryText.textContent : '';
        if (!text) return;

        navigator.clipboard.writeText(text).then(() => {
            aiSummaryCopyIcon.className = 'bi bi-clipboard-check';
            aiSummaryCopyBtn.title = 'Copied!';
            setTimeout(() => {
                aiSummaryCopyIcon.className = 'bi bi-clipboard';
                aiSummaryCopyBtn.title = 'Copy to clipboard';
            }, 2000);
        }).catch(() => {
            // Fallback for browsers without clipboard API
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity  = '0';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            aiSummaryCopyIcon.className = 'bi bi-clipboard-check';
            setTimeout(() => { aiSummaryCopyIcon.className = 'bi bi-clipboard'; }, 2000);
        });
    });

    // Shared spinner CSS injected here (review.php has no existing <style> with aiSlideIn)
    if (!document.getElementById('aiReviewAnimStyle')) {
        const s = document.createElement('style');
        s.id = 'aiReviewAnimStyle';
        s.textContent = `
            @keyframes aiSlideIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
            .ai-loading { display:flex; align-items:center; gap:.75rem; padding:.875rem 0; color:#c4b5fd; font-size:.875rem; }
            .ai-spinner { width:1.1rem;height:1.1rem;border:2px solid rgba(196,181,253,.3);border-top-color:#c4b5fd;border-radius:50%;animation:spin .7s linear infinite;flex-shrink:0; }
            @keyframes spin { to{transform:rotate(360deg)} }
            .ai-error { display:flex;align-items:center;gap:.6rem;padding:.75rem 0;color:#f87171;font-size:.875rem; }
        `;
        document.head.appendChild(s);
    }
})();
</script>
