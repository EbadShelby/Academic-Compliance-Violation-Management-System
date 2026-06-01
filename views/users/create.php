<?php
/**
 * views/users/create.php
 * Admin — Create New User form
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
.field-input.is-invalid { border-color: rgba(248,113,113,.6); }
.field-input::placeholder { color: rgba(148,163,184,.5); }
select.field-input option { background: #1e293b; }
.field-error { font-size: .8125rem; color: #f87171; margin-top: .3rem; }
.field-hint  { font-size: .75rem; color: var(--text-muted); margin-top: .3rem; }

.form-actions {
    display: flex; gap: .75rem; align-items: center; margin-top: 1.75rem; flex-wrap: wrap;
}
.btn-primary-custom {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .625rem 1.375rem;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
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

.pw-toggle-wrap { position: relative; }
.pw-toggle-wrap .field-input { padding-right: 2.75rem; }
.pw-toggle {
    position: absolute; right: .75rem; top: 50%; transform: translateY(-50%);
    background: none; border: none; color: var(--text-muted); cursor: pointer;
    font-size: 1rem; padding: 0; transition: color .15s;
}
.pw-toggle:hover { color: var(--text-primary); }
</style>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" style="margin-bottom:1.25rem;">
    <ol class="d-flex gap-2 list-unstyled mb-0" style="font-size:.875rem;color:var(--text-muted);">
        <li><a href="<?= APP_URL ?>/admin/users" style="color:var(--text-muted);text-decoration:none;">Users</a></li>
        <li style="opacity:.4;">/</li>
        <li style="color:var(--text-primary);">Create New User</li>
    </ol>
</nav>

<div class="form-card">
    <form method="POST" action="<?= APP_URL ?>/admin/users" id="createUserForm" novalidate>
        <?= csrf_field() ?>

        <!-- Personal Info -->
        <div class="form-section-title">Personal Information</div>
        <div class="row g-3 mb-2">
            <div class="col-md-6">
                <div class="field-group">
                    <label class="field-label" for="first_name">First Name <span class="req">*</span></label>
                    <input type="text" id="first_name" name="first_name" class="field-input <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                        value="<?= htmlspecialchars($old['first_name'] ?? '') ?>"
                        placeholder="e.g. John" maxlength="100" autocomplete="given-name" required>
                    <?php if (isset($errors['first_name'])): ?>
                    <div class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['first_name']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="field-group">
                    <label class="field-label" for="last_name">Last Name <span class="req">*</span></label>
                    <input type="text" id="last_name" name="last_name" class="field-input <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                        value="<?= htmlspecialchars($old['last_name'] ?? '') ?>"
                        placeholder="e.g. Doe" maxlength="100" autocomplete="family-name" required>
                    <?php if (isset($errors['last_name'])): ?>
                    <div class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['last_name']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="field-group">
            <label class="field-label" for="email">Email Address <span class="req">*</span></label>
            <input type="email" id="email" name="email" class="field-input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                placeholder="user@institution.edu" maxlength="191" autocomplete="email" required>
            <?php if (isset($errors['email'])): ?>
            <div class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Role & Student ID -->
        <div class="form-section-title mt-4">Role & Identity</div>
        <div class="row g-3 mb-2">
            <div class="col-md-6">
                <div class="field-group">
                    <label class="field-label" for="role_id">Role <span class="req">*</span></label>
                    <select id="role_id" name="role_id" class="field-input <?= isset($errors['role_id']) ? 'is-invalid' : '' ?>" required>
                        <option value="">— Select a role —</option>
                        <?php foreach ($roles as $rid => $rname): ?>
                        <option value="<?= $rid ?>" <?= ((int)($old['role_id'] ?? 0) === $rid) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($rname) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['role_id'])): ?>
                    <div class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['role_id']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="field-group">
                    <label class="field-label" for="student_id">Student ID</label>
                    <input type="text" id="student_id" name="student_id" class="field-input <?= isset($errors['student_id']) ? 'is-invalid' : '' ?>"
                        value="<?= htmlspecialchars($old['student_id'] ?? '') ?>"
                        placeholder="e.g. 2024-00123" maxlength="64">
                    <div class="field-hint">Leave blank for admin/teacher accounts.</div>
                    <?php if (isset($errors['student_id'])): ?>
                    <div class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['student_id']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Password -->
        <div class="form-section-title mt-4">Security</div>
        <div class="field-group">
            <label class="field-label" for="password">Password <span class="req">*</span></label>
            <div class="pw-toggle-wrap">
                <input type="password" id="password" name="password" class="field-input <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                    placeholder="Min. 8 chars, 1 uppercase, 1 number" autocomplete="new-password" required>
                <button type="button" class="pw-toggle" id="pwToggle" aria-label="Toggle password visibility">
                    <i class="bi bi-eye" id="pwToggleIcon"></i>
                </button>
            </div>
            <div class="field-hint">Minimum 8 characters with at least one uppercase letter and one number.</div>
            <?php if (isset($errors['password'])): ?>
            <div class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['password']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <button type="submit" class="btn-primary-custom" id="submitCreateBtn">
                <i class="bi bi-person-plus-fill"></i> Create User
            </button>
            <a href="<?= APP_URL ?>/admin/users" class="btn-cancel">
                <i class="bi bi-x"></i> Cancel
            </a>
        </div>
    </form>
</div>

<script>
// Password visibility toggle
const pwInput = document.getElementById('password');
const pwIcon  = document.getElementById('pwToggleIcon');
document.getElementById('pwToggle')?.addEventListener('click', function () {
    const show = pwInput.type === 'password';
    pwInput.type      = show ? 'text' : 'password';
    pwIcon.className  = show ? 'bi bi-eye-slash' : 'bi bi-eye';
});
</script>
