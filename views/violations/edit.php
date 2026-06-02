<?php
/**
 * views/violations/edit.php
 * Edit a violation report
 */
?>

<div class="viol-create-wrap">

    <!-- ── Page header ────────────────────────────────────────────────────── -->
    <div class="page-header-bar d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1" style="color:var(--text-primary);">Edit Violation #<?= $violation['id'] ?></h4>
            <p class="text-muted mb-0" style="font-size:.875rem;">
                Update the details of the violation report.
            </p>
        </div>
        <a href="<?= APP_URL ?>/violations/<?= $violation['id'] ?>" class="btn-secondary-custom">
            <i class="bi bi-arrow-left"></i> Back to Violation
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

    <form action="<?= APP_URL ?>/violations/<?= $violation['id'] ?>" method="POST" id="violationForm">
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
                                    <?= ($old['student_id'] ?? $violation['student_id']) == $student['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?>
                                    <?= $student['student_id'] ? ' (' . htmlspecialchars($student['student_id']) . ')' : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['student_id'])): ?>
                            <div class="field-error"><?= htmlspecialchars($errors['student_id']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Incident date -->
                        <div class="mb-0">
                            <label for="incident_date" class="form-label-custom">
                                Incident Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="incident_date" id="incident_date"
                                   class="form-control-custom <?= isset($errors['incident_date']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($old['incident_date'] ?? $violation['incident_date']) ?>"
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
                                      required><?= htmlspecialchars($old['description'] ?? $violation['description']) ?></textarea>
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
                                        <?= ($old['type'] ?? $violation['type']) === $value ? 'selected' : '' ?>>
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
                                        <?= ($old['severity'] ?? $violation['severity']) === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['severity'])): ?>
                                <div class="field-error"><?= htmlspecialchars($errors['severity']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>

            </div><!-- /col-lg-8 -->

            <!-- ══ RIGHT COLUMN ══════════════════════════════════════════════ -->
            <div class="col-lg-4">

                <!-- Submit panel -->
                <div class="form-card mb-4 sticky-lg-top" style="top:1.5rem;">
                    <div class="form-card-header">
                        <i class="bi bi-save-fill"></i>
                        <span>Save Changes</span>
                    </div>
                    <div class="form-card-body">

                        <p class="text-muted mb-3" style="font-size:.8125rem;">
                            Update the details for this violation report. The action will be recorded in the audit logs.
                        </p>

                        <!-- Severity indicator -->
                        <div class="severity-preview mb-3" id="severityPreview" style="display:none;">
                            <span class="severity-label">Selected severity:</span>
                            <span class="severity-badge" id="severityBadge"></span>
                        </div>

                        <button type="submit" class="btn-submit-primary w-100 mb-2" id="submitBtn">
                            <i class="bi bi-check-circle"></i> Save Changes
                        </button>
                        <a href="<?= APP_URL ?>/violations/<?= $violation['id'] ?>" class="btn-cancel-link w-100 text-center d-block">
                            Cancel
                        </a>

                    </div>
                </div>

            </div><!-- /col-lg-4 -->

        </div><!-- /row -->

    </form>
</div>

<style>
/* ── Edit form styles ──────────────────────────────────────────────────── */
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
    background: var(--glass-bg-25);
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
    background: var(--glass-bg-5);
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
.form-select-custom option { background: var(--surface-card); color: var(--text-primary); }
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

/* Severity preview */
.severity-preview {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .5rem .875rem;
    background: var(--glass-bg-4);
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
    background: var(--glass-bg-6);
    border: 1px solid var(--border-subtle);
    border-radius: .625rem;
    color: var(--text-muted);
    font-size: .875rem;
    font-weight: 500;
    text-decoration: none;
    transition: background .15s, color .15s;
}
.btn-secondary-custom:hover { background: var(--glass-bg-10); color: var(--text-primary); }
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
    if(desc) updateCounter();

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
    if(sevSelect) updateSeverity();

    // ── Submit guard ───────────────────────────────────────────────────────
    const form      = document.getElementById('violationForm');
    const submitBtn = document.getElementById('submitBtn');

    form && form.addEventListener('submit', function () {
        submitBtn.disabled   = true;
        submitBtn.innerHTML  = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Saving…';
    });
})();
</script>
