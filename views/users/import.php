<?php
/**
 * views/users/import.php
 * Admin — Bulk CSV User Import form
 */
?>

<style>
.form-card {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: 1rem;
    padding: 2rem;
    max-width: 720px;
}
.form-section-title {
    font-size: .6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: var(--text-muted);
    margin-bottom: 1.25rem;
    padding-bottom: .5rem;
    border-bottom: 1px solid var(--border-subtle);
}
.field-group { margin-bottom: 1.25rem; }
.field-label {
    display: block;
    font-size: .8125rem;
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: .4rem;
}
.field-label .req { color: #f87171; margin-left: .2rem; }
.field-input {
    width: 100%;
    padding: .625rem .875rem;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--border-subtle);
    border-radius: .625rem;
    color: var(--text-primary);
    font-size: .9375rem;
    transition: border-color .15s, box-shadow .15s;
    outline: none;
}
.field-input:focus {
    border-color: rgba(79,70,229,.6);
    box-shadow: 0 0 0 3px rgba(79,70,229,.12);
}
.field-input::file-selector-button {
    background: rgba(255,255,255,.1);
    border: 1px solid var(--border-subtle);
    border-radius: .4rem;
    color: var(--text-primary);
    padding: .4rem .8rem;
    margin-right: 1rem;
    cursor: pointer;
    transition: background .15s;
}
.field-input::file-selector-button:hover { background: rgba(255,255,255,.2); }

.field-hint  { font-size: .75rem; color: var(--text-muted); margin-top: .5rem; }

.form-actions {
    display: flex; gap: .75rem; align-items: center; margin-top: 1.75rem; flex-wrap: wrap;
}
.btn-primary-custom {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .625rem 1.375rem;
    background: linear-gradient(135deg, #10b981, #059669);
    border: none; border-radius: .625rem;
    color: #fff; font-size: .9375rem; font-weight: 600;
    cursor: pointer; text-decoration: none;
    transition: opacity .15s, transform .1s;
}
.btn-primary-custom:hover { opacity: .9; transform: translateY(-1px); color: #fff; }
.btn-cancel {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .625rem 1.25rem;
    background: none;
    border: 1px solid var(--border-subtle);
    border-radius: .625rem;
    color: var(--text-muted); font-size: .9375rem; font-weight: 500;
    cursor: pointer; text-decoration: none;
    transition: background .15s, border-color .15s, color .15s;
}
.btn-cancel:hover { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.2); color: var(--text-primary); }

.instructions-box {
    background: rgba(16,185,129,.05);
    border: 1px solid rgba(16,185,129,.2);
    border-radius: .75rem;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}
.instructions-box h5 {
    font-size: .9375rem;
    color: #34d399;
    margin-top: 0;
    margin-bottom: .75rem;
    font-weight: 600;
}
.instructions-box ul { margin-bottom: 0; padding-left: 1.25rem; color: var(--text-primary); font-size: .875rem; }
.instructions-box ul li { margin-bottom: .4rem; }
.instructions-box ul li code {
    background: rgba(0,0,0,.2); padding: .1rem .3rem; border-radius: .25rem; color: #6ee7b7;
}

.error-list {
    background: rgba(248,113,113,.1);
    border: 1px solid rgba(248,113,113,.3);
    border-radius: .5rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
}
.error-list ul { margin-bottom: 0; padding-left: 1.25rem; color: #fca5a5; font-size: .875rem; }
.error-list ul li { margin-bottom: .25rem; }
</style>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" style="margin-bottom:1.25rem;">
    <ol class="d-flex gap-2 list-unstyled mb-0" style="font-size:.875rem;color:var(--text-muted);">
        <li><a href="<?= APP_URL ?>/admin/users" style="color:var(--text-muted);text-decoration:none;">Users</a></li>
        <li style="opacity:.4;">/</li>
        <li style="color:var(--text-primary);">Import CSV</li>
    </ol>
</nav>

<div class="form-card">
    
    <div class="instructions-box">
        <h5><i class="bi bi-info-circle-fill me-2"></i> CSV Format Instructions</h5>
        <p style="font-size: .875rem; color: var(--text-muted); margin-bottom: .75rem;">Your CSV file must include a header row with the following exact column names (order does not matter):</p>
        <ul>
            <li><code>first_name</code> (Required)</li>
            <li><code>last_name</code> (Required)</li>
            <li><code>email</code> (Required, must be unique)</li>
            <li><code>role</code> (Required: <code>student</code>, <code>teacher</code>, or <code>admin</code>)</li>
            <li><code>student_id</code> (Optional, used for students)</li>
            <li><code>password</code> (Optional, default is <code>TempPass123!</code> if left empty)</li>
        </ul>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-list">
            <div style="font-weight: 600; color: #f87171; margin-bottom: .5rem;"><i class="bi bi-exclamation-triangle-fill"></i> Import Errors (These rows were skipped):</div>
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/admin/users/import" enctype="multipart/form-data" id="importUserForm">
        <?= csrf_field() ?>

        <div class="form-section-title">Upload File</div>
        <div class="field-group">
            <label class="field-label" for="csv_file">Select CSV File <span class="req">*</span></label>
            <input type="file" id="csv_file" name="csv_file" class="field-input" accept=".csv, text/csv, application/vnd.ms-excel" required>
            <div class="field-hint">Maximum file size depends on server settings (typically 2MB). Only .csv extensions allowed.</div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <button type="submit" class="btn-primary-custom" id="submitImportBtn">
                <i class="bi bi-upload"></i> Process Import
            </button>
            <a href="<?= APP_URL ?>/admin/users" class="btn-cancel">
                <i class="bi bi-x"></i> Cancel
            </a>
        </div>
    </form>
</div>
