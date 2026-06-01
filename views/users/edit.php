<?php
/**
 * views/users/edit.php
 * Admin — Edit User form (also handles password reset)
 */
?>

<style>
.form-card {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: 1rem;
    padding: 2rem;
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

/* Status toggle */
.toggle-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: .875rem 1rem;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--border-subtle);
    border-radius: .625rem;
}
.form-switch-custom .form-check-input {
    width: 2.5em; height: 1.35em; cursor: pointer;
}
.form-check-input:checked { background-color: #34d399; border-color: #34d399; }

/* Password reset panel */
.reset-pw-panel {
    background: rgba(248,113,113,.05);
    border: 1px solid rgba(248,113,113,.2);
    border-radius: .875rem;
    padding: 1.5rem;
}
.reset-pw-panel .form-section-title { color: #f87171; border-color: rgba(248,113,113,.2); }
.btn-danger-custom {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5rem 1.125rem;
    background: rgba(248,113,113,.15);
    border: 1px solid rgba(248,113,113,.4);
    border-radius: .625rem;
    color: #f87171; font-size: .875rem; font-weight: 600;
    cursor: pointer; text-decoration: none;
    transition: background .15s, opacity .15s;
}
.btn-danger-custom:hover { opacity: .85; }
</style>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" style="margin-bottom:1.25rem;">
    <ol class="d-flex gap-2 list-unstyled mb-0" style="font-size:.875rem;color:var(--text-muted);">
        <li><a href="<?= APP_URL ?>/admin/users" style="color:var(--text-muted);text-decoration:none;">Users</a></li>
        <li style="opacity:.4;">/</li>
        <li><a href="<?= APP_URL ?>/admin/users/<?= $user['id'] ?>" style="color:var(--text-muted);text-decoration:none;">
            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
        </a></li>
        <li style="opacity:.4;">/</li>
        <li style="color:var(--text-primary);">Edit</li>
    </ol>
</nav>

<div class="row g-3">

    <!-- ── Main edit form ──────────────────────────────────────────────────── -->
    <div class="col-lg-8">
        <div class="form-card">
            <form method="POST" action="<?= APP_URL ?>/admin/users/<?= $user['id'] ?>" id="editUserForm" novalidate>
                <?= csrf_field() ?>

                <!-- Personal Info -->
                <div class="form-section-title">Personal Information</div>
                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="first_name">First Name <span class="req">*</span></label>
                            <input type="text" id="first_name" name="first_name"
                                class="field-input <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($merged['first_name'] ?? '') ?>"
                                maxlength="100" required>
                            <?php if (isset($errors['first_name'])): ?>
                            <div class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['first_name']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="last_name">Last Name <span class="req">*</span></label>
                            <input type="text" id="last_name" name="last_name"
                                class="field-input <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($merged['last_name'] ?? '') ?>"
                                maxlength="100" required>
                            <?php if (isset($errors['last_name'])): ?>
                            <div class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['last_name']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="email">Email Address <span class="req">*</span></label>
                    <input type="email" id="email" name="email"
                        class="field-input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                        value="<?= htmlspecialchars($merged['email'] ?? '') ?>"
                        maxlength="191" required>
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
                            <select id="role_id" name="role_id"
                                class="field-input <?= isset($errors['role_id']) ? 'is-invalid' : '' ?>" required>
                                <option value="">— Select a role —</option>
                                <?php foreach ($roles as $rid => $rname): ?>
                                <option value="<?= $rid ?>" <?= ((int)($merged['role_id'] ?? 0) === $rid) ? 'selected' : '' ?>>
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
                            <input type="text" id="student_id" name="student_id"
                                class="field-input <?= isset($errors['student_id']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($merged['student_id'] ?? '') ?>"
                                maxlength="64">
                            <div class="field-hint">Leave blank for admin/teacher accounts.</div>
                            <?php if (isset($errors['student_id'])): ?>
                            <div class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['student_id']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Account Status -->
                <div class="form-section-title mt-4">Account Status</div>
                <div class="toggle-row mb-3">
                    <div>
                        <div style="font-size:.9375rem;font-weight:600;">Account Active</div>
                        <div style="font-size:.8125rem;color:var(--text-muted);">Inactive users cannot log in.</div>
                    </div>
                    <div class="form-check form-switch form-switch-custom mb-0">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                            <?= ((int)($merged['is_active'] ?? 1) === 1) ? 'checked' : '' ?>>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="form-section-title mt-4">Change Password</div>
                <div class="field-group">
                    <label class="field-label" for="password">New Password <span style="color:var(--text-muted);font-weight:400;">(leave blank to keep current)</span></label>
                    <div class="pw-toggle-wrap">
                        <input type="password" id="password" name="password"
                            class="field-input <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                            placeholder="Leave blank to keep current password"
                            autocomplete="new-password">
                        <button type="button" class="pw-toggle" id="pwToggle" aria-label="Toggle password visibility">
                            <i class="bi bi-eye" id="pwToggleIcon"></i>
                        </button>
                    </div>
                    <div class="field-hint">If set: min. 8 characters, 1 uppercase letter, 1 number.</div>
                    <?php if (isset($errors['password'])): ?>
                    <div class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['password']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn-primary-custom" id="submitEditBtn">
                        <i class="bi bi-check-circle-fill"></i> Save Changes
                    </button>
                    <a href="<?= APP_URL ?>/admin/users/<?= $user['id'] ?>" class="btn-cancel">
                        <i class="bi bi-x"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Side: Quick password reset ───────────────────────────────────────── -->
    <div class="col-lg-4">
        <div class="reset-pw-panel">
            <div class="form-section-title">
                <i class="bi bi-key-fill me-1"></i> Quick Password Reset
            </div>
            <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1rem;">
                Set a specific new password for this account immediately.
            </p>
            <form method="POST" action="<?= APP_URL ?>/admin/users/<?= $user['id'] ?>/delete" id="resetPwForm"
                onsubmit="return confirm('Reset this user\'s password?')">
                <?= csrf_field() ?>
                <input type="hidden" name="_action" value="reset_password">

                <div class="field-group">
                    <label class="field-label" for="new_password">New Password <span class="req">*</span></label>
                    <div class="pw-toggle-wrap">
                        <input type="password" id="new_password" name="new_password"
                            class="field-input <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>"
                            placeholder="Min. 8 chars, 1 uppercase, 1 number"
                            autocomplete="new-password">
                        <button type="button" class="pw-toggle" id="pwToggle2" aria-label="Toggle new password visibility">
                            <i class="bi bi-eye" id="pwToggleIcon2"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['new_password'])): ?>
                    <div class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['new_password']) ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-danger-custom w-100 justify-content-center">
                    <i class="bi bi-key-fill"></i> Reset Password
                </button>
            </form>
        </div>
    </div>

</div>

<script>
// Main form password toggle
(function () {
    const pwInput = document.getElementById('password');
    const pwIcon  = document.getElementById('pwToggleIcon');
    document.getElementById('pwToggle')?.addEventListener('click', function () {
        const show = pwInput.type === 'password';
        pwInput.type     = show ? 'text' : 'password';
        pwIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
})();

// Reset form password toggle
(function () {
    const pwInput = document.getElementById('new_password');
    const pwIcon  = document.getElementById('pwToggleIcon2');
    document.getElementById('pwToggle2')?.addEventListener('click', function () {
        const show = pwInput.type === 'password';
        pwInput.type     = show ? 'text' : 'password';
        pwIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
})();
</script>
