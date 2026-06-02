<?php
/**
 * views/violations/create.php
 * File a new violation report — Teacher / Admin only
 */
?>

<div class="viol-create-wrap">

    <!-- ── Page header ────────────────────────────────────────────────────── -->
    <div class="page-header-bar d-flex align-items-center justify-content-between mb-4">
        <div>
            <p class="text-muted mb-0" style="font-size:.875rem;">
                Complete all required fields and attach any supporting evidence.
            </p>
        </div>
        <a href="<?= APP_URL ?>/violations" class="btn-secondary-custom">
            <i class="bi bi-arrow-left"></i> Back to Violations
        </a>
    </div>

    <!-- ── Validation errors banner ───────────────────────────────────────── -->
    <?php if (!empty($errors)): ?>
    <div class="alert-custom alert-danger-custom mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1 ps-3" style="font-size:.875rem;">
                <?php foreach ($errors as $field => $msg): ?>
                <li><?= htmlspecialchars($msg) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/violations" method="POST" enctype="multipart/form-data" novalidate id="violationForm">
        <?= csrf_field() ?>

        <div class="row g-4">

            <!-- ══ LEFT COLUMN ═══════════════════════════════════════════════ -->
            <div class="col-lg-8">

                <!-- ── Section 1: Parties ─────────────────────────────────── -->
                <div class="form-card mb-4">
                    <div class="form-card-header">
                        <i class="bi bi-person-lines-fill"></i>
                        <span>Parties Involved</span>
                    </div>
                    <div class="form-card-body">

                        <!-- Student select -->
                        <div class="mb-3">
                            <label for="student_id" class="form-label-custom">
                                Student <span class="text-danger">*</span>
                            </label>
                            <select name="student_id" id="student_id"
                                    class="form-select-custom <?= isset($errors['student_id']) ? 'is-invalid' : '' ?>"
                                    required>
                                <option value="">— Select a student —</option>
                                <?php foreach ($students as $student): ?>
                                <option value="<?= $student['id'] ?>"
                                    <?= ($old['student_id'] ?? 0) == $student['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?>
                                    <?= $student['student_id'] ? ' (' . htmlspecialchars($student['student_id']) . ')' : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['student_id'])): ?>
                            <div class="field-error"><?= htmlspecialchars($errors['student_id']) ?></div>
                            <?php endif; ?>
                            <?php if (empty($students)): ?>
                            <div class="field-hint text-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                No active students found in the system.
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Incident date -->
                        <div class="mb-0">
                            <label for="incident_date" class="form-label-custom">
                                Incident Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="incident_date" id="incident_date"
                                   class="form-control-custom <?= isset($errors['incident_date']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($old['incident_date'] ?? date('Y-m-d')) ?>"
                                   max="<?= date('Y-m-d') ?>"
                                   required>
                            <?php if (isset($errors['incident_date'])): ?>
                            <div class="field-error"><?= htmlspecialchars($errors['incident_date']) ?></div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

                <!-- ── Section 2: Violation Details ───────────────────────── -->
                <div class="form-card mb-4">
                    <div class="form-card-header">
                        <i class="bi bi-exclamation-octagon-fill"></i>
                        <span>Violation Details</span>
                    </div>
                    <div class="form-card-body">

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label-custom">
                                Violation Description <span class="text-danger">*</span>
                            </label>
                            <textarea name="description" id="description" rows="6"
                                      class="form-control-custom <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                                      placeholder="Provide a detailed account of the incident (minimum 20 characters)…"
                                      required><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <?php if (isset($errors['description'])): ?>
                                <div class="field-error"><?= htmlspecialchars($errors['description']) ?></div>
                                <?php else: ?>
                                <div class="field-hint">Minimum 20 characters. Be factual and objective.</div>
                                <?php endif; ?>
                                <small class="text-muted" id="descCounter">0 / 5000</small>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Category -->
                            <div class="col-sm-6">
                                <label for="type" class="form-label-custom">
                                    Category <span class="text-danger">*</span>
                                </label>
                                <select name="type" id="type"
                                        class="form-select-custom <?= isset($errors['type']) ? 'is-invalid' : '' ?>"
                                        required>
                                    <option value="">— Select category —</option>
                                    <?php foreach ($categories as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>"
                                        <?= ($old['type'] ?? '') === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['type'])): ?>
                                <div class="field-error"><?= htmlspecialchars($errors['type']) ?></div>
                                <?php endif; ?>

                                <!-- ── AI Category Suggestion ────────── -->
                                <div class="ai-cat-assess-wrap mt-2">
                                    <button type="button" id="aiCatBtn" class="btn-ai-cat">
                                        <i class="bi bi-stars"></i>
                                        <span id="aiCatBtnText">AI Suggest Category</span>
                                    </button>
                                </div>
                                <!-- AI category result card -->
                                <div id="aiCatResultCard" class="ai-cat-result-card" style="display:none;" aria-live="polite">
                                    <div id="aiCatLoading" class="ai-loading" style="display:none;">
                                        <div class="ai-spinner"></div>
                                        <span>Classifying violation…</span>
                                    </div>
                                    <div id="aiCatError" class="ai-error" style="display:none;">
                                        <i class="bi bi-exclamation-circle"></i>
                                        <span id="aiCatErrorText"></span>
                                    </div>
                                    <div id="aiCatSuccess" style="display:none;">
                                        <div class="ai-result-header">
                                            <i class="bi bi-stars ai-icon"></i>
                                            <span class="ai-result-label">AI Category Suggestion</span>
                                            <span id="aiCatConfPill" class="ai-confidence-pill"></span>
                                        </div>
                                        <div class="ai-result-body">
                                            <div class="ai-suggested-severity">
                                                <span class="ai-sev-prefix">Suggested:</span>
                                                <span id="aiCatBadge" class="ai-cat-badge"></span>
                                            </div>
                                            <p id="aiCatReasoning" class="ai-reasoning"></p>
                                        </div>
                                        <div class="ai-result-actions">
                                            <button type="button" id="aiCatAcceptBtn" class="btn-ai-accept">
                                                <i class="bi bi-check2-circle"></i> Apply to Category Field
                                            </button>
                                            <button type="button" id="aiCatDismissBtn" class="btn-ai-dismiss">
                                                Dismiss
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Severity -->
                            <div class="col-sm-6">
                                <label for="severity" class="form-label-custom">
                                    Severity <span class="text-danger">*</span>
                                </label>
                                <select name="severity" id="severity"
                                        class="form-select-custom <?= isset($errors['severity']) ? 'is-invalid' : '' ?>"
                                        required>
                                    <option value="">— Select severity —</option>
                                    <?php foreach ($severities as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>"
                                        <?= ($old['severity'] ?? '') === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['severity'])): ?>
                                <div class="field-error"><?= htmlspecialchars($errors['severity']) ?></div>
                                <?php endif; ?>

                                <!-- ── AI Severity Assessment ──────────────────────── -->
                                <div class="ai-assess-wrap mt-2">
                                    <button type="button" id="aiAssessBtn" class="btn-ai-assess">
                                        <i class="bi bi-stars"></i>
                                        <span id="aiAssessBtnText">AI Assess Severity</span>
                                    </button>
                                </div>

                                <!-- AI result card (hidden until response) -->
                                <div id="aiResultCard" class="ai-result-card" style="display:none;" aria-live="polite">
                                    <!-- Loading state -->
                                    <div id="aiLoading" class="ai-loading" style="display:none;">
                                        <div class="ai-spinner"></div>
                                        <span>Analysing violation description…</span>
                                    </div>

                                    <!-- Error state -->
                                    <div id="aiError" class="ai-error" style="display:none;">
                                        <i class="bi bi-exclamation-circle"></i>
                                        <span id="aiErrorText"></span>
                                    </div>

                                    <!-- Success state -->
                                    <div id="aiSuccess" style="display:none;">
                                        <div class="ai-result-header">
                                            <i class="bi bi-stars ai-icon"></i>
                                            <span class="ai-result-label">AI Severity Suggestion</span>
                                            <span id="aiConfidencePill" class="ai-confidence-pill"></span>
                                        </div>
                                        <div class="ai-result-body">
                                            <div class="ai-suggested-severity">
                                                <span class="ai-sev-prefix">Suggested:</span>
                                                <span id="aiSeverityBadge" class="ai-severity-badge"></span>
                                            </div>
                                            <p id="aiReasoning" class="ai-reasoning"></p>
                                        </div>
                                        <div class="ai-result-actions">
                                            <button type="button" id="aiAcceptBtn" class="btn-ai-accept">
                                                <i class="bi bi-check2-circle"></i> Apply to Severity Field
                                            </button>
                                            <button type="button" id="aiDismissBtn" class="btn-ai-dismiss">
                                                Dismiss
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ── Section 3: Evidence Upload ──────────────────────────── -->
                <div class="form-card mb-4">
                    <div class="form-card-header">
                        <i class="bi bi-paperclip"></i>
                        <span>Evidence Files <span style="font-weight:400;font-size:.8rem;opacity:.7;">(optional)</span></span>
                    </div>
                    <div class="form-card-body">

                        <?php
                        // Collect any file-specific errors
                        $fileErrors = array_filter($errors, fn($k) => str_starts_with($k, 'evidence_'), ARRAY_FILTER_USE_KEY);
                        ?>
                        <?php if (!empty($fileErrors)): ?>
                        <div class="alert-custom alert-danger-custom mb-3" style="font-size:.85rem;">
                            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($fileErrors as $msg): ?>
                                <li><?= htmlspecialchars($msg) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <div class="upload-zone" id="uploadZone">
                            <i class="bi bi-cloud-arrow-up upload-zone-icon"></i>
                            <p class="mb-1 fw-semibold">Drag & drop files here or click to browse</p>
                            <p class="text-muted mb-3" style="font-size:.8125rem;">
                                Accepted: JPG, PNG, GIF, WEBP, PDF, DOC, DOCX, TXT — Max 10 MB each
                            </p>
                            <input type="file" name="evidence[]" id="evidenceInput"
                                   class="upload-file-input"
                                   multiple
                                   accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.txt">
                            <label for="evidenceInput" class="btn-upload-browse">
                                <i class="bi bi-folder2-open"></i> Browse Files
                            </label>
                        </div>

                        <!-- File preview list -->
                        <div id="filePreviewList" class="file-preview-list mt-3"></div>

                    </div>
                </div>

            </div><!-- /col-lg-8 -->

            <!-- ══ RIGHT COLUMN ══════════════════════════════════════════════ -->
            <div class="col-lg-4">

                <!-- Submit panel -->
                <div class="form-card mb-4 sticky-lg-top" style="top:1.5rem;">
                    <div class="form-card-header">
                        <i class="bi bi-send-fill"></i>
                        <span>Submit Report</span>
                    </div>
                    <div class="form-card-body">

                        <p class="text-muted mb-3" style="font-size:.8125rem;">
                            By submitting, you confirm that the information provided is
                            accurate to the best of your knowledge.
                        </p>

                        <!-- Severity indicator -->
                        <div class="severity-preview mb-3" id="severityPreview" style="display:none;">
                            <span class="severity-label">Selected severity:</span>
                            <span class="severity-badge" id="severityBadge"></span>
                        </div>

                        <button type="submit" class="btn-submit-primary w-100 mb-2" id="submitBtn">
                            <i class="bi bi-send"></i> Submit Violation Report
                        </button>
                        <a href="<?= APP_URL ?>/violations" class="btn-cancel-link w-100 text-center d-block">
                            Cancel
                        </a>

                    </div>
                </div>

                <!-- Guidelines -->
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="bi bi-info-circle-fill"></i>
                        <span>Reporting Guidelines</span>
                    </div>
                    <div class="form-card-body">
                        <ul class="guidelines-list">
                            <li><i class="bi bi-check2"></i> Be factual and objective</li>
                            <li><i class="bi bi-check2"></i> Include the exact date of incident</li>
                            <li><i class="bi bi-check2"></i> Attach supporting evidence where available</li>
                            <li><i class="bi bi-check2"></i> Do not include personal bias</li>
                            <li><i class="bi bi-shield-check"></i> All reports are logged and audited</li>
                        </ul>
                    </div>
                </div>

            </div><!-- /col-lg-4 -->

        </div><!-- /row -->

    </form>
</div>

<style>
/* ── Create form styles ──────────────────────────────────────────────────── */
.form-card {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: 1rem;
    overflow: hidden;
}
.form-card-header {
    display: flex;
    align-items: center;
    gap: .625rem;
    padding: .875rem 1.25rem;
    border-bottom: 1px solid var(--border-subtle);
    font-size: .9375rem;
    font-weight: 700;
    color: var(--text-primary);
    background: rgba(255,255,255,.025);
}
.form-card-header i { color: var(--brand-primary); font-size: 1.0625rem; }
.form-card-body { padding: 1.25rem; }

/* Labels & controls */
.form-label-custom {
    display: block;
    font-size: .8125rem;
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: .375rem;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.form-control-custom,
.form-select-custom {
    width: 100%;
    padding: .625rem .875rem;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--border-subtle);
    border-radius: .625rem;
    color: var(--text-primary);
    font-size: .9375rem;
    font-family: inherit;
    transition: border-color .15s, box-shadow .15s;
    outline: none;
}
.form-control-custom:focus,
.form-select-custom:focus {
    border-color: var(--brand-primary);
    box-shadow: 0 0 0 3px rgba(79,70,229,.2);
}
.form-control-custom.is-invalid,
.form-select-custom.is-invalid {
    border-color: #f87171;
}
.form-select-custom option { background: #1e293b; color: var(--text-primary); }
textarea.form-control-custom { resize: vertical; min-height: 120px; line-height: 1.6; }

.field-error { font-size: .8rem; color: #f87171; margin-top: .25rem; }
.field-hint  { font-size: .8rem; color: var(--text-muted); margin-top: .25rem; }

/* Alert */
.alert-custom {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .875rem 1rem;
    border-radius: .75rem;
    font-size: .875rem;
}
.alert-danger-custom {
    background: rgba(248,113,113,.1);
    border: 1px solid rgba(248,113,113,.3);
    color: #f87171;
}
.alert-custom i { font-size: 1.1rem; flex-shrink: 0; }

/* Upload zone */
.upload-zone {
    border: 2px dashed var(--border-subtle);
    border-radius: .75rem;
    padding: 2rem 1.25rem;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    position: relative;
}
.upload-zone:hover,
.upload-zone.drag-over {
    border-color: var(--brand-primary);
    background: rgba(79,70,229,.05);
}
.upload-zone-icon { font-size: 2.5rem; color: var(--text-muted); display: block; margin-bottom: .5rem; }
.upload-file-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}
.btn-upload-browse {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .5rem 1.25rem;
    background: rgba(79,70,229,.15);
    border: 1px solid rgba(79,70,229,.3);
    border-radius: .5rem;
    color: #a5b4fc;
    font-size: .875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.btn-upload-browse:hover { background: rgba(79,70,229,.3); }

/* File preview */
.file-preview-list { display: flex; flex-direction: column; gap: .5rem; }
.file-preview-item {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .625rem .875rem;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--border-subtle);
    border-radius: .625rem;
    font-size: .875rem;
}
.file-preview-item .file-icon { font-size: 1.25rem; flex-shrink: 0; }
.file-preview-item .file-info { flex: 1; min-width: 0; }
.file-preview-item .file-name { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.file-preview-item .file-size { color: var(--text-muted); font-size: .75rem; }
.file-preview-item .file-remove {
    background: none;
    border: none;
    color: #f87171;
    cursor: pointer;
    font-size: 1.1rem;
    padding: 0 .25rem;
    flex-shrink: 0;
}

/* Severity preview */
.severity-preview {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .5rem .875rem;
    background: rgba(255,255,255,.04);
    border-radius: .5rem;
    font-size: .8125rem;
}
.severity-label { color: var(--text-muted); }
.severity-badge {
    padding: .2rem .6rem;
    border-radius: .375rem;
    font-weight: 700;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .06em;
}
.sev-minor    { background: rgba(52,211,153,.15); color: #34d399; }
.sev-moderate { background: rgba(251,191,36,.15);  color: #fbbf24; }
.sev-major    { background: rgba(249,115,22,.15);  color: #f97316; }
.sev-critical { background: rgba(248,113,113,.15); color: #f87171; }

/* ── AI Severity Assessment ───────────────────────────────────────────────── */
.ai-assess-wrap {
    display: flex;
    align-items: center;
    gap: .75rem;
    flex-wrap: wrap;
}
.btn-ai-assess {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .45rem 1rem;
    background: linear-gradient(135deg, rgba(139,92,246,.25), rgba(79,70,229,.2));
    border: 1px solid rgba(139,92,246,.4);
    border-radius: .625rem;
    color: #c4b5fd;
    font-size: .875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    white-space: nowrap;
}
.btn-ai-assess:hover:not(:disabled) {
    background: linear-gradient(135deg, rgba(139,92,246,.4), rgba(79,70,229,.35));
    border-color: rgba(139,92,246,.7);
    color: #ddd6fe;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(139,92,246,.2);
}
.btn-ai-assess:disabled {
    opacity: .6;
    cursor: not-allowed;
}
.btn-ai-assess .bi-stars { font-size: 1rem; }
.ai-hint-text {
    font-size: .775rem;
    color: var(--text-muted);
    opacity: .8;
}

/* Result card */
.ai-result-card {
    margin-top: .75rem;
    border: 1px solid rgba(139,92,246,.35);
    border-radius: .75rem;
    background: rgba(139,92,246,.07);
    overflow: hidden;
    animation: aiSlideIn .25s ease;
}
@keyframes aiSlideIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.ai-loading {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: 1rem 1.25rem;
    color: #c4b5fd;
    font-size: .875rem;
}
.ai-spinner {
    width: 1.1rem;
    height: 1.1rem;
    border: 2px solid rgba(196,181,253,.3);
    border-top-color: #c4b5fd;
    border-radius: 50%;
    animation: spin .7s linear infinite;
    flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }
.ai-error {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .875rem 1.25rem;
    color: #f87171;
    font-size: .875rem;
}
.ai-result-header {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .75rem 1.25rem;
    border-bottom: 1px solid rgba(139,92,246,.2);
}
.ai-icon { color: #a78bfa; font-size: 1.1rem; }
.ai-result-label {
    font-size: .8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #a78bfa;
    flex: 1;
}
.ai-confidence-pill {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    padding: .15rem .55rem;
    border-radius: 999px;
}
.conf-high   { background: rgba(52,211,153,.2); color: #34d399; border: 1px solid rgba(52,211,153,.3); }
.conf-medium { background: rgba(251,191,36,.2); color: #fbbf24; border: 1px solid rgba(251,191,36,.3); }
.conf-low    { background: rgba(249,115,22,.2); color: #f97316; border: 1px solid rgba(249,115,22,.3); }
.ai-result-body { padding: .875rem 1.25rem; }
.ai-suggested-severity {
    display: flex;
    align-items: center;
    gap: .6rem;
    margin-bottom: .625rem;
}
.ai-sev-prefix { font-size: .8rem; color: var(--text-muted); }
.ai-severity-badge {
    padding: .25rem .75rem;
    border-radius: .4rem;
    font-weight: 700;
    font-size: .8rem;
    text-transform: uppercase;
    letter-spacing: .07em;
}
.ai-reasoning {
    font-size: .825rem;
    color: var(--text-muted);
    margin: 0;
    line-height: 1.55;
    font-style: italic;
}
.ai-result-actions {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .75rem 1.25rem;
    border-top: 1px solid rgba(139,92,246,.2);
}
.btn-ai-accept {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .4rem .95rem;
    background: linear-gradient(135deg, #7c3aed, #4f46e5);
    border: none;
    border-radius: .5rem;
    color: #fff;
    font-size: .8125rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .15s, transform .1s;
}
.btn-ai-accept:hover { opacity: .88; transform: translateY(-1px); }
.btn-ai-dismiss {
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: .8rem;
    cursor: pointer;
    padding: .3rem .6rem;
    border-radius: .375rem;
    transition: color .15s;
}
.btn-ai-dismiss:hover { color: var(--text-primary); }

/* AI Category button (Phase 2) */
.ai-cat-assess-wrap {
    display: flex;
    align-items: center;
    gap: .75rem;
}
.btn-ai-cat {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .35rem .85rem;
    background: linear-gradient(135deg, rgba(6,182,212,.2), rgba(14,165,233,.15));
    border: 1px solid rgba(6,182,212,.35);
    border-radius: .5rem;
    color: #67e8f9;
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    white-space: nowrap;
}
.btn-ai-cat:hover:not(:disabled) {
    background: linear-gradient(135deg, rgba(6,182,212,.35), rgba(14,165,233,.3));
    border-color: rgba(6,182,212,.6);
    color: #a5f3fc;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(6,182,212,.15);
}
.btn-ai-cat:disabled { opacity: .6; cursor: not-allowed; }
.btn-ai-cat .bi-stars { font-size: .9rem; }

.ai-cat-result-card {
    margin-top: .5rem;
    border: 1px solid rgba(6,182,212,.3);
    border-radius: .75rem;
    background: rgba(6,182,212,.06);
    overflow: hidden;
    animation: aiSlideIn .25s ease;
}
.ai-cat-badge {
    padding: .2rem .65rem;
    border-radius: .375rem;
    font-weight: 700;
    font-size: .78rem;
    text-transform: none;
    letter-spacing: .02em;
    background: rgba(6,182,212,.18);
    color: #67e8f9;
    border: 1px solid rgba(6,182,212,.3);
}

/* Flash animation when AI suggestion is accepted */
@keyframes aiAcceptPulse {
    0%   { box-shadow: 0 0 0 0 rgba(52,211,153,.5); border-color: #34d399; }
    60%  { box-shadow: 0 0 0 6px rgba(52,211,153,0); border-color: #34d399; }
    100% { box-shadow: none; border-color: var(--border-subtle); }
}
.ai-accepted-flash {
    animation: aiAcceptPulse 1.1s ease forwards;
}

/* Submit / cancel */
.btn-submit-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    padding: .75rem 1.5rem;
    background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
    border: none;
    border-radius: .625rem;
    color: #fff;
    font-size: .9375rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .15s, transform .1s;
}
.btn-submit-primary:hover { opacity: .9; transform: translateY(-1px); }
.btn-submit-primary:active { transform: translateY(0); }
.btn-cancel-link {
    color: var(--text-muted);
    font-size: .875rem;
    text-decoration: none;
    padding: .5rem;
}
.btn-cancel-link:hover { color: var(--text-primary); }

.btn-secondary-custom {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .5rem 1rem;
    background: rgba(255,255,255,.06);
    border: 1px solid var(--border-subtle);
    border-radius: .625rem;
    color: var(--text-muted);
    font-size: .875rem;
    font-weight: 500;
    text-decoration: none;
    transition: background .15s, color .15s;
}
.btn-secondary-custom:hover { background: rgba(255,255,255,.1); color: var(--text-primary); }

/* Guidelines */
.guidelines-list {
    list-style: none;
    padding: 0; margin: 0;
    display: flex;
    flex-direction: column;
    gap: .5rem;
}
.guidelines-list li {
    display: flex;
    align-items: flex-start;
    gap: .5rem;
    font-size: .8125rem;
    color: var(--text-muted);
}
.guidelines-list li i { color: #34d399; flex-shrink: 0; margin-top: .1rem; }
.guidelines-list li i.bi-shield-check { color: #a5b4fc; }
</style>

<script>
(function () {
    // ── Description character counter ──────────────────────────────────────
    const desc    = document.getElementById('description');
    const counter = document.getElementById('descCounter');

    function updateCounter() {
        const len  = desc.value.length;
        counter.textContent = len + ' / 5000';
        counter.style.color = len > 4800 ? '#f87171' : '';
    }
    desc && desc.addEventListener('input', updateCounter);
    updateCounter();

    // ── AI Severity Assessment ─────────────────────────────────────────────
    const aiAssessBtn    = document.getElementById('aiAssessBtn');
    const aiAssessBtnTxt = document.getElementById('aiAssessBtnText');
    const aiResultCard   = document.getElementById('aiResultCard');
    const aiLoading      = document.getElementById('aiLoading');
    const aiError        = document.getElementById('aiError');
    const aiErrorText    = document.getElementById('aiErrorText');
    const aiSuccess      = document.getElementById('aiSuccess');
    const aiSeverityBadge = document.getElementById('aiSeverityBadge');
    const aiConfPill     = document.getElementById('aiConfidencePill');
    const aiReasoning    = document.getElementById('aiReasoning');
    const aiAcceptBtn    = document.getElementById('aiAcceptBtn');
    const aiDismissBtn   = document.getElementById('aiDismissBtn');
    const sevSelect      = document.getElementById('severity');
    const typeSelect     = document.getElementById('type');

    let lastSuggestedSeverity = null;

    const severityLabels = { minor: 'Minor', moderate: 'Moderate', major: 'Major', critical: 'Critical' };
    const severityClasses = { minor: 'sev-minor', moderate: 'sev-moderate', major: 'sev-major', critical: 'sev-critical' };
    const confidenceClasses = { high: 'conf-high', medium: 'conf-medium', low: 'conf-low' };

    function showAiState(state) {
        aiResultCard.style.display = 'block';
        aiLoading.style.display = state === 'loading' ? 'flex' : 'none';
        aiError.style.display   = state === 'error'   ? 'flex' : 'none';
        aiSuccess.style.display = state === 'success' ? 'block' : 'none';
    }

    function hideAiCard() {
        aiResultCard.style.display = 'none';
        lastSuggestedSeverity = null;
    }

    aiAssessBtn && aiAssessBtn.addEventListener('click', async function () {
        const description = desc ? desc.value.trim() : '';
        const type        = typeSelect ? typeSelect.value : '';

        if (description.length < 20) {
            showAiState('error');
            aiErrorText.textContent = 'Please write at least 20 characters in the description before assessing.';
            return;
        }

        // Set button loading state
        aiAssessBtn.disabled = true;
        aiAssessBtnTxt.textContent = 'Analysing…';
        showAiState('loading');

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const response = await fetch('<?= APP_URL ?>/ai/assess-severity', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ description, type })
            });

            const json = await response.json();

            if (!json.success) {
                showAiState('error');
                aiErrorText.textContent = json.error || 'AI assessment failed. Please try again.';
                return;
            }

            const { severity, confidence, reasoning } = json.data;
            lastSuggestedSeverity = severity;

            // Populate success card
            aiSeverityBadge.textContent = severityLabels[severity] || severity;
            aiSeverityBadge.className   = 'ai-severity-badge ' + (severityClasses[severity] || '');

            aiConfPill.textContent = (confidence || 'medium').charAt(0).toUpperCase() + (confidence || 'medium').slice(1) + ' confidence';
            aiConfPill.className   = 'ai-confidence-pill ' + (confidenceClasses[confidence] || 'conf-medium');

            aiReasoning.textContent = reasoning || '';

            showAiState('success');

        } catch (err) {
            showAiState('error');
            aiErrorText.textContent = 'Network error. Please check your connection and try again.';
            console.error('AI assess error:', err);
        } finally {
            aiAssessBtn.disabled = false;
            aiAssessBtnTxt.textContent = 'AI Assess Severity';
        }
    });

    // Accept button: apply suggested severity to the form select
    aiAcceptBtn && aiAcceptBtn.addEventListener('click', function () {
        if (!lastSuggestedSeverity || !sevSelect) return;
        sevSelect.value = lastSuggestedSeverity;
        // Trigger the existing severity badge preview update
        sevSelect.dispatchEvent(new Event('change'));
        // Visual feedback on the select
        sevSelect.classList.add('ai-accepted-flash');
        setTimeout(() => sevSelect.classList.remove('ai-accepted-flash'), 1200);
        hideAiCard();
    });

    // Dismiss button
    aiDismissBtn && aiDismissBtn.addEventListener('click', hideAiCard);

    // Re-hide the card if the user clears the description significantly
    desc && desc.addEventListener('input', function () {
        if (lastSuggestedSeverity && desc.value.trim().length < 10) {
            hideAiCard();
        }
    });

    // ── AI Category Classification (Phase 2) ───────────────────────────────
    const aiCatBtn        = document.getElementById('aiCatBtn');
    const aiCatBtnTxt     = document.getElementById('aiCatBtnText');
    const aiCatResultCard = document.getElementById('aiCatResultCard');
    const aiCatLoading    = document.getElementById('aiCatLoading');
    const aiCatError      = document.getElementById('aiCatError');
    const aiCatErrorText  = document.getElementById('aiCatErrorText');
    const aiCatSuccess    = document.getElementById('aiCatSuccess');
    const aiCatBadge      = document.getElementById('aiCatBadge');
    const aiCatConfPill   = document.getElementById('aiCatConfPill');
    const aiCatReasoning  = document.getElementById('aiCatReasoning');
    const aiCatAcceptBtn  = document.getElementById('aiCatAcceptBtn');
    const aiCatDismissBtn = document.getElementById('aiCatDismissBtn');

    let lastSuggestedCategory = null;

    function showCatState(state) {
        aiCatResultCard.style.display = 'block';
        aiCatLoading.style.display  = state === 'loading'  ? 'flex'  : 'none';
        aiCatError.style.display    = state === 'error'    ? 'flex'  : 'none';
        aiCatSuccess.style.display  = state === 'success'  ? 'block' : 'none';
    }

    function hideCatCard() {
        aiCatResultCard.style.display = 'none';
        lastSuggestedCategory = null;
    }

    aiCatBtn && aiCatBtn.addEventListener('click', async function () {
        const description = desc ? desc.value.trim() : '';

        if (description.length < 20) {
            showCatState('error');
            aiCatErrorText.textContent = 'Please write at least 20 characters in the description before classifying.';
            return;
        }

        aiCatBtn.disabled = true;
        aiCatBtnTxt.textContent = 'Classifying…';
        showCatState('loading');

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const response = await fetch('<?= APP_URL ?>/ai/classify-category', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ description })
            });

            const json = await response.json();

            if (!json.success) {
                showCatState('error');
                aiCatErrorText.textContent = json.error || 'AI classification failed. Please try again.';
                return;
            }

            const { category, confidence, reasoning } = json.data;
            lastSuggestedCategory = category;

            aiCatBadge.textContent  = category;
            aiCatBadge.className    = 'ai-cat-badge';

            aiCatConfPill.textContent = (confidence || 'medium').charAt(0).toUpperCase() + (confidence || 'medium').slice(1) + ' confidence';
            aiCatConfPill.className   = 'ai-confidence-pill ' + ({'high':'conf-high','medium':'conf-medium','low':'conf-low'}[confidence] || 'conf-medium');

            aiCatReasoning.textContent = reasoning || '';

            showCatState('success');

        } catch (err) {
            showCatState('error');
            aiCatErrorText.textContent = 'Network error. Please check your connection and try again.';
            console.error('AI category error:', err);
        } finally {
            aiCatBtn.disabled = false;
            aiCatBtnTxt.textContent = 'AI Suggest Category';
        }
    });

    // Apply suggested category
    aiCatAcceptBtn && aiCatAcceptBtn.addEventListener('click', function () {
        if (!lastSuggestedCategory || !typeSelect) return;
        typeSelect.value = lastSuggestedCategory;
        typeSelect.classList.add('ai-accepted-flash');
        setTimeout(() => typeSelect.classList.remove('ai-accepted-flash'), 1200);
        hideCatCard();
    });

    aiCatDismissBtn && aiCatDismissBtn.addEventListener('click', hideCatCard);

    // Auto-hide category card if description is cleared
    desc && desc.addEventListener('input', function () {
        if (lastSuggestedCategory && desc.value.trim().length < 10) hideCatCard();
    });

    // ── Severity badge preview ─────────────────────────────────────────────
    const sevPreview = document.getElementById('severityPreview');
    const sevBadge   = document.getElementById('severityBadge');
    const sevLabels  = { minor:'Minor', moderate:'Moderate', major:'Major', critical:'Critical' };

    function updateSeverity() {
        const val = sevSelect.value;
        if (!val) { sevPreview.style.display = 'none'; return; }
        sevPreview.style.display = 'flex';
        sevBadge.textContent     = sevLabels[val] || val;
        sevBadge.className       = 'severity-badge sev-' + val;
    }
    sevSelect && sevSelect.addEventListener('change', updateSeverity);
    updateSeverity();

    // ── File upload & preview ──────────────────────────────────────────────
    const fileInput   = document.getElementById('evidenceInput');
    const previewList = document.getElementById('filePreviewList');
    const uploadZone  = document.getElementById('uploadZone');
    let   selectedFiles = [];

    const mimeIcons = {
        'image': 'bi-file-image',
        'application/pdf': 'bi-file-pdf',
        'application/msword': 'bi-file-word',
        'application/vnd.openxmlformats': 'bi-file-word',
        'text': 'bi-file-text',
    };

    function getFileIcon(type) {
        for (const [key, icon] of Object.entries(mimeIcons)) {
            if (type.startsWith(key)) return icon;
        }
        return 'bi-file-earmark';
    }

    function formatSize(bytes) {
        if (bytes < 1024)       return bytes + ' B';
        if (bytes < 1048576)    return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    function renderPreviews() {
        previewList.innerHTML = '';
        selectedFiles.forEach((file, i) => {
            const item = document.createElement('div');
            item.className = 'file-preview-item';
            item.innerHTML = `
                <i class="bi ${getFileIcon(file.type)} file-icon"></i>
                <div class="file-info">
                    <div class="file-name">${escHtml(file.name)}</div>
                    <div class="file-size">${formatSize(file.size)}</div>
                </div>
                <button type="button" class="file-remove" data-idx="${i}" title="Remove">
                    <i class="bi bi-x-circle-fill"></i>
                </button>`;
            previewList.appendChild(item);
        });

        // Rebind remove buttons and sync real input
        previewList.querySelectorAll('.file-remove').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = parseInt(this.dataset.idx);
                selectedFiles.splice(idx, 1);
                syncInputFiles();
                renderPreviews();
            });
        });
    }

    function syncInputFiles() {
        const dt = new DataTransfer();
        selectedFiles.forEach(f => dt.items.add(f));
        fileInput.files = dt.files;
    }

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    fileInput && fileInput.addEventListener('change', function () {
        Array.from(this.files).forEach(f => selectedFiles.push(f));
        syncInputFiles();
        renderPreviews();
    });

    // Drag & drop
    uploadZone && uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('drag-over'); });
    uploadZone && uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('drag-over'));
    uploadZone && uploadZone.addEventListener('drop', e => {
        e.preventDefault();
        uploadZone.classList.remove('drag-over');
        Array.from(e.dataTransfer.files).forEach(f => selectedFiles.push(f));
        syncInputFiles();
        renderPreviews();
    });

    // ── Submit guard ───────────────────────────────────────────────────────
    const form      = document.getElementById('violationForm');
    const submitBtn = document.getElementById('submitBtn');

    form && form.addEventListener('submit', function () {
        submitBtn.disabled   = true;
        submitBtn.innerHTML  = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Submitting…';
    });
})();
</script>
