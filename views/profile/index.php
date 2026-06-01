<?php
/**
 * views/profile/index.php
 * Self-Service User Profile
 */
?>

<style>
.form-card {
    background: var(--surface-card);
    border: 1px solid var(--border-subtle);
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
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
.field-input:disabled {
    background: rgba(255,255,255,.02);
    color: rgba(255,255,255,.4);
    cursor: not-allowed;
}
.field-input.is-invalid { border-color: rgba(248,113,113,.6); }
.field-error { font-size: .8125rem; color: #f87171; margin-top: .3rem; }
.field-hint  { font-size: .75rem; color: var(--text-muted); margin-top: .3rem; }

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

.pw-toggle-wrap { position: relative; }
.pw-toggle-wrap .field-input { padding-right: 2.75rem; }
.pw-toggle {
    position: absolute; right: .75rem; top: 50%; transform: translateY(-50%);
    background: none; border: none; color: var(--text-muted); cursor: pointer;
    font-size: 1rem; padding: 0; transition: color .15s;
}
.pw-toggle:hover { color: var(--text-primary); }

.profile-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.profile-avatar {
    width: 80px; height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--brand-primary), var(--brand-accent));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
    text-transform: uppercase;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}
.profile-info h2 { margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
.profile-info p { margin: 0; font-size: .9375rem; color: var(--text-muted); text-transform: capitalize; }
</style>

<div class="profile-header">
    <div class="profile-avatar">
        <?= mb_substr($user['first_name'] ?? 'U', 0, 1) ?>
    </div>
    <div class="profile-info">
        <h2><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h2>
        <p><?= htmlspecialchars($user['role_name'] ?? 'User') ?></p>
    </div>
</div>

<div class="row g-4">

    <!-- ── Basic Info ──────────────────────────────────────────────────────── -->
    <div class="col-lg-6">
        <div class="form-card h-100">
            <form method="POST" action="<?= APP_URL ?>/profile/update">
                <?= csrf_field() ?>

                <div class="form-section-title">Basic Information</div>
                
                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="first_name">First Name <span class="req">*</span></label>
                            <input type="text" id="first_name" name="first_name"
                                class="field-input <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($old['first_name'] ?? $user['first_name']) ?>"
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
                                value="<?= htmlspecialchars($old['last_name'] ?? $user['last_name']) ?>"
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
                        value="<?= htmlspecialchars($old['email'] ?? $user['email']) ?>"
                        maxlength="191" required>
                    <?php if (isset($errors['email'])): ?>
                    <div class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($user['student_id']) || $user['role'] === 'student'): ?>
                <div class="field-group">
                    <label class="field-label" for="student_id">Student ID</label>
                    <input type="text" id="student_id" name="student_id" class="field-input"
                        value="<?= htmlspecialchars($user['student_id'] ?? '') ?>" disabled>
                    <div class="field-hint">Student IDs are managed by the administration and cannot be changed here.</div>
                </div>
                <?php endif; ?>

                <div class="mt-4">
                    <button type="submit" class="btn-primary-custom">
                        <i class="bi bi-save"></i> Save Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Security / Password ─────────────────────────────────────────────── -->
    <div class="col-lg-6">
        <div class="form-card h-100">
            <form method="POST" action="<?= APP_URL ?>/profile/password">
                <?= csrf_field() ?>

                <div class="form-section-title">Change Password</div>

                <div class="field-group">
                    <label class="field-label" for="current_password">Current Password <span class="req">*</span></label>
                    <div class="pw-toggle-wrap">
                        <input type="password" id="current_password" name="current_password"
                            class="field-input <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" required>
                        <button type="button" class="pw-toggle" aria-label="Toggle password visibility" data-target="current_password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['current_password'])): ?>
                    <div class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['current_password']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="field-group">
                    <label class="field-label" for="new_password">New Password <span class="req">*</span></label>
                    <div class="pw-toggle-wrap">
                        <input type="password" id="new_password" name="new_password"
                            class="field-input <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" required>
                        <button type="button" class="pw-toggle" aria-label="Toggle password visibility" data-target="new_password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="field-hint">Min. 8 characters, 1 uppercase letter, 1 number.</div>
                    <?php if (isset($errors['new_password'])): ?>
                    <div class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['new_password']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="field-group">
                    <label class="field-label" for="confirm_password">Confirm New Password <span class="req">*</span></label>
                    <div class="pw-toggle-wrap">
                        <input type="password" id="confirm_password" name="confirm_password"
                            class="field-input <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" required>
                        <button type="button" class="pw-toggle" aria-label="Toggle password visibility" data-target="confirm_password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['confirm_password'])): ?>
                    <div class="field-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errors['confirm_password']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn-primary-custom" style="background: linear-gradient(135deg, #059669, #10b981);">
                        <i class="bi bi-shield-lock"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
// Password toggles
document.querySelectorAll('.pw-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = this.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    });
});
</script>
