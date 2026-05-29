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
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-0">
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

    // ── Severity badge preview ─────────────────────────────────────────────
    const sevSelect  = document.getElementById('severity');
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
