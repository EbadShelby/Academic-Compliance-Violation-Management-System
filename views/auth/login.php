<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sign in to the Academic Compliance & Violation Management System.">
    <title><?= htmlspecialchars($title ?? 'Login — ' . APP_NAME) ?></title>

    <!-- Bootstrap 5.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ── Reset & base ─────────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brand-primary:   #4f46e5;   /* Indigo */
            --brand-secondary: #7c3aed;   /* Violet */
            --brand-accent:    #06b6d4;   /* Cyan  */
            --surface-dark:    #0f172a;
            --surface-card:    rgba(255,255,255,0.06);
            --border-subtle:   rgba(255,255,255,0.12);
            --text-primary:    #f8fafc;
            --text-muted:      #94a3b8;
            --error-red:       #f87171;
            --success-green:   #34d399;
            --input-bg:        rgba(255,255,255,0.08);
            --input-border:    rgba(255,255,255,0.18);
            --input-focus:     #4f46e5;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', system-ui, sans-serif;
            background-color: var(--surface-dark);
            color: var(--text-primary);
        }

        /* ── Animated gradient background ────────────────────────────────── */
        .login-bg {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(79,70,229,.35) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 20%, rgba(124,58,237,.3)  0%, transparent 55%),
                radial-gradient(ellipse at 60% 80%, rgba(6,182,212,.2)   0%, transparent 55%),
                var(--surface-dark);
        }

        /* Subtle grid overlay */
        .login-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        /* Floating orb animations */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: .4;
            animation: drift 12s ease-in-out infinite alternate;
        }
        .orb-1 { width: 400px; height: 400px; background: var(--brand-primary);  top: -10%; left: -5%;  animation-delay: 0s; }
        .orb-2 { width: 300px; height: 300px; background: var(--brand-secondary); bottom: -5%; right: 0%; animation-delay: -4s; }
        .orb-3 { width: 200px; height: 200px; background: var(--brand-accent);    top: 40%;   right: 10%; animation-delay: -8s; }

        @keyframes drift {
            from { transform: translate(0, 0)   scale(1);   }
            to   { transform: translate(30px, 20px) scale(1.08); }
        }

        /* ── Card ─────────────────────────────────────────────────────────── */
        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            background: var(--surface-card);
            border: 1px solid var(--border-subtle);
            border-radius: 1.5rem;
            padding: 2.75rem 2.5rem;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.06),
                0 24px 64px rgba(0,0,0,.5),
                0 0 80px rgba(79,70,229,0.12);
            animation: slideUp .5s cubic-bezier(.16,1,.3,1) both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0);    }
        }

        /* ── Logo / Brand ─────────────────────────────────────────────────── */
        .brand-icon {
            width: 56px;
            height: 56px;
            border-radius: 1rem;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: #fff;
            box-shadow: 0 8px 24px rgba(79,70,229,.45);
            margin-bottom: 1.25rem;
        }

        .login-title {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: .25rem;
        }

        .login-subtitle {
            font-size: .875rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
        }

        /* ── Alerts ───────────────────────────────────────────────────────── */
        .alert-custom {
            border-radius: .75rem;
            padding: .875rem 1rem;
            font-size: .875rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: .6rem;
            animation: fadeIn .3s ease;
        }
        .alert-error {
            background: rgba(248,113,113,.12);
            border: 1px solid rgba(248,113,113,.3);
            color: var(--error-red);
        }
        .alert-success {
            background: rgba(52,211,153,.12);
            border: 1px solid rgba(52,211,153,.3);
            color: var(--success-green);
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* ── Form labels ──────────────────────────────────────────────────── */
        .form-label-custom {
            display: block;
            font-size: .8125rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: .5rem;
        }

        /* ── Inputs ───────────────────────────────────────────────────────── */
        .input-wrapper {
            position: relative;
            margin-bottom: 1.25rem;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1rem;
            pointer-events: none;
            transition: color .2s;
        }

        .form-control-custom {
            width: 100%;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: .75rem;
            color: var(--text-primary);
            font-family: inherit;
            font-size: .9375rem;
            padding: .8125rem 1rem .8125rem 2.8rem;
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
            -webkit-appearance: none;
        }
        .form-control-custom::placeholder { color: rgba(148,163,184,.5); }
        .form-control-custom:focus {
            border-color: var(--input-focus);
            background: rgba(79,70,229,.08);
            box-shadow: 0 0 0 3px rgba(79,70,229,.2);
        }
        .form-control-custom:focus ~ .input-icon { color: var(--brand-primary); }

        /* Password toggle */
        .pw-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1rem;
            transition: color .2s;
            line-height: 1;
        }
        .pw-toggle:hover { color: var(--text-primary); }

        /* ── Remember me ──────────────────────────────────────────────────── */
        .remember-row {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: 1.75rem;
        }
        .remember-row input[type="checkbox"] {
            width: 1rem; height: 1rem;
            accent-color: var(--brand-primary);
            cursor: pointer;
        }
        .remember-label {
            font-size: .875rem;
            color: var(--text-muted);
            cursor: pointer;
            user-select: none;
        }

        /* ── Submit button ────────────────────────────────────────────────── */
        .btn-login {
            width: 100%;
            padding: .875rem 1.5rem;
            border: none;
            border-radius: .75rem;
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
            color: #fff;
            font-family: inherit;
            font-size: .9375rem;
            font-weight: 600;
            letter-spacing: .01em;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 20px rgba(79,70,229,.4);
        }
        .btn-login:hover  { opacity: .92; transform: translateY(-1px); box-shadow: 0 8px 28px rgba(79,70,229,.5); }
        .btn-login:active { transform: translateY(0);   opacity: .85; }
        .btn-login:disabled { opacity: .6; cursor: not-allowed; transform: none; }

        /* Shimmer on hover */
        .btn-login::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,.15) 50%, transparent 60%);
            transform: translateX(-100%);
            transition: transform .5s;
        }
        .btn-login:hover::before { transform: translateX(100%); }

        /* ── Divider ──────────────────────────────────────────────────────── */
        .divider {
            text-align: center;
            position: relative;
            margin: 1.75rem 0 1.25rem;
            font-size: .8125rem;
            color: var(--text-muted);
        }
        .divider::before, .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: calc(50% - 2.5rem);
            height: 1px;
            background: var(--border-subtle);
        }
        .divider::before { left: 0; }
        .divider::after  { right: 0; }

        /* ── Footer text ──────────────────────────────────────────────────── */
        .login-footer {
            text-align: center;
            font-size: .8125rem;
            color: var(--text-muted);
            margin-top: 1.5rem;
        }

        /* ── Ethics Modal ─────────────────────────────────────────────────── */
        .ethics-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.72);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            opacity: 0;
            transition: opacity .3s ease;
            pointer-events: none;
        }
        .ethics-overlay.visible {
            opacity: 1;
            pointer-events: all;
        }
        .ethics-modal {
            width: 100%;
            max-width: 520px;
            background: #0f172a;
            border: 1px solid rgba(79, 70, 229, 0.45);
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 0 0 1px rgba(79,70,229,.15), 0 32px 80px rgba(0,0,0,.7), 0 0 80px rgba(79,70,229,.15);
            transform: translateY(12px);
            transition: transform .35s cubic-bezier(.16,1,.3,1);
        }
        .ethics-overlay.visible .ethics-modal {
            transform: translateY(0);
        }
        .ethics-modal-header {
            display: flex;
            align-items: center;
            gap: .875rem;
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, rgba(79,70,229,.18), rgba(124,58,237,.12));
            border-bottom: 1px solid rgba(79,70,229,.25);
        }
        .ethics-modal-icon {
            width: 42px; height: 42px;
            border-radius: .75rem;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 4px 16px rgba(79,70,229,.4);
        }
        .ethics-modal-title {
            font-size: 1.0625rem;
            font-weight: 700;
            color: #f8fafc;
            margin: 0;
            line-height: 1.25;
        }
        .ethics-modal-subtitle {
            font-size: .75rem;
            color: #94a3b8;
            margin: .1rem 0 0;
        }
        .ethics-modal-body {
            padding: 1.5rem;
        }
        .ethics-section {
            margin-bottom: 1rem;
        }
        .ethics-section:last-of-type {
            margin-bottom: 0;
        }
        .ethics-section-title {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .09em;
            color: #a5b4fc;
            margin-bottom: .4rem;
            display: flex;
            align-items: center;
            gap: .35rem;
        }
        .ethics-section-text {
            font-size: .8375rem;
            color: #cbd5e1;
            line-height: 1.65;
            margin: 0;
        }
        .ethics-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,.07);
            margin: 1rem 0;
        }
        .ethics-checkbox-row {
            display: flex;
            align-items: flex-start;
            gap: .625rem;
            margin-top: 1.25rem;
            padding: .875rem 1rem;
            background: rgba(79,70,229,.08);
            border: 1px solid rgba(79,70,229,.2);
            border-radius: .75rem;
        }
        .ethics-checkbox-row input[type="checkbox"] {
            width: 1rem; height: 1rem;
            accent-color: #4f46e5;
            flex-shrink: 0;
            margin-top: .15rem;
            cursor: pointer;
        }
        .ethics-checkbox-label {
            font-size: .825rem;
            color: #94a3b8;
            cursor: pointer;
            line-height: 1.5;
            user-select: none;
        }
        .ethics-checkbox-label strong { color: #c7d2fe; }
        .ethics-modal-footer {
            padding: 1rem 1.5rem 1.5rem;
            display: flex;
            gap: .75rem;
            flex-direction: column;
        }
        .ethics-accept-btn {
            width: 100%;
            padding: .8rem 1.5rem;
            border: none;
            border-radius: .75rem;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            font-size: .9375rem;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s, transform .15s;
            box-shadow: 0 4px 20px rgba(79,70,229,.4);
            opacity: .45;
            pointer-events: none;
        }
        .ethics-accept-btn.enabled {
            opacity: 1;
            pointer-events: all;
        }
        .ethics-accept-btn.enabled:hover { opacity: .9; transform: translateY(-1px); }
        .ethics-note {
            text-align: center;
            font-size: .75rem;
            color: rgba(148, 163, 184, 0.7);
        }

        /* ── Responsive ───────────────────────────────────────────────────── */
        @media (max-width: 480px) {
            .login-card { padding: 2rem 1.5rem; }
            .ethics-modal-body { padding: 1.125rem; }
            .ethics-modal-footer { padding: .875rem 1.125rem 1.25rem; }
        }
    </style>
</head>
<body>

<!-- ══ ETHICS & COMPLIANCE NOTICE MODAL ═══════════════════════════════════ -->
<div class="ethics-overlay" id="ethicsOverlay" role="dialog" aria-modal="true" aria-labelledby="ethicsModalTitle">
    <div class="ethics-modal" id="ethicsModal">

        <div class="ethics-modal-header">
            <div class="ethics-modal-icon" aria-hidden="true">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div>
                <h2 class="ethics-modal-title" id="ethicsModalTitle">Ethics &amp; Compliance Notice</h2>
                <p class="ethics-modal-subtitle">Please review before continuing</p>
            </div>
        </div>

        <div class="ethics-modal-body">

            <div class="ethics-section">
                <div class="ethics-section-title">
                    <i class="bi bi-eye-fill"></i> Purpose of this System
                </div>
                <p class="ethics-section-text">
                    This system is used exclusively for recording, managing, and resolving academic compliance violations.
                    All actions performed here are subject to institutional policy and are permanently logged in a tamper-evident audit trail.
                </p>
            </div>

            <hr class="ethics-divider">

            <div class="ethics-section">
                <div class="ethics-section-title">
                    <i class="bi bi-person-lock"></i> Data Privacy
                </div>
                <p class="ethics-section-text">
                    Student records and violation data are confidential and protected under applicable data privacy laws.
                    Unauthorized access, disclosure, or misuse of information is a disciplinary and legal offense.
                </p>
            </div>

            <hr class="ethics-divider">

            <div class="ethics-section">
                <div class="ethics-section-title">
                    <i class="bi bi-check2-circle"></i> Responsible Use
                </div>
                <p class="ethics-section-text">
                    You are expected to act in good faith. Reports must be factual, objective, and free from personal bias.
                    Filing a false or malicious report is a serious violation of institutional ethics.
                </p>
            </div>

            <div class="ethics-checkbox-row">
                <input type="checkbox" id="ethicsAck" name="ethicsAck">
                <label for="ethicsAck" class="ethics-checkbox-label">
                    I have read and understood this notice. I agree to use this system
                    <strong>responsibly, ethically, and in accordance with institutional policies.</strong>
                </label>
            </div>

        </div>

        <div class="ethics-modal-footer">
            <button type="button" class="ethics-accept-btn" id="ethicsAcceptBtn" disabled>
                <i class="bi bi-check-lg me-2" aria-hidden="true"></i>I Acknowledge &amp; Continue
            </button>
            <p class="ethics-note">
                <i class="bi bi-lock-fill me-1" aria-hidden="true"></i>
                This notice is shown once per session.
            </p>
        </div>

    </div>
</div>

<div class="login-bg">
    <!-- Decorative orbs -->
    <div class="orb orb-1" aria-hidden="true"></div>
    <div class="orb orb-2" aria-hidden="true"></div>
    <div class="orb orb-3" aria-hidden="true"></div>

    <div class="login-card">

        <!-- Brand -->
        <div class="brand-icon" aria-hidden="true">
            <i class="bi bi-shield-lock-fill"></i>
        </div>

        <h1 class="login-title">Welcome back</h1>
        <p class="login-subtitle">Sign in to <?= htmlspecialchars(APP_NAME) ?></p>



        <!-- Error alert -->
        <?php if (!empty($error)): ?>
        <div class="alert-custom alert-error" role="alert">
            <i class="bi bi-exclamation-circle-fill flex-shrink-0" style="margin-top:1px;"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <!-- Success alert (e.g. after logout) -->
        <?php if (!empty($success)): ?>
        <div class="alert-custom alert-success" role="alert">
            <i class="bi bi-check-circle-fill flex-shrink-0" style="margin-top:1px;"></i>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form id="loginForm" method="POST" action="<?= APP_URL ?>/login" novalidate>
            <?= csrf_field() ?>

            <!-- Email -->
            <div>
                <label for="email" class="form-label-custom">Email address</label>
                <div class="input-wrapper">
                    <i class="bi bi-envelope input-icon" aria-hidden="true"></i>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control-custom"
                        placeholder="you@school.edu"
                        value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                        autocomplete="email"
                        required
                        autofocus
                    >
                </div>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="form-label-custom">Password</label>
                <div class="input-wrapper">
                    <i class="bi bi-lock input-icon" aria-hidden="true"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control-custom"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <button
                        type="button"
                        class="pw-toggle"
                        id="pwToggle"
                        aria-label="Toggle password visibility"
                        aria-pressed="false"
                    >
                        <i class="bi bi-eye" id="pwToggleIcon" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <!-- Remember me & Forgot Password -->
            <div class="remember-row" style="justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: .5rem;">
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember" class="remember-label">Keep me signed in</label>
                </div>
                <a href="<?= APP_URL ?>/forgot-password" class="remember-label" style="text-decoration: none; color: var(--brand-primary); font-weight: 500;">Forgot Password?</a>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-login" id="btnLogin">
                <span id="btnLoginText">
                    <i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i>Sign in
                </span>
                <span id="btnLoginSpinner" class="d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Signing in…
                </span>
            </button>

        </form>

        <!-- Footer -->
        <p class="login-footer">
            <i class="bi bi-shield-check me-1" aria-hidden="true"></i>
            Secured with end-to-end encryption
        </p>

    </div><!-- /.login-card -->
</div><!-- /.login-bg -->

<!-- Bootstrap JS bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // ── Password visibility toggle ────────────────────────────────────────────
    const pwInput   = document.getElementById('password');
    const pwToggle  = document.getElementById('pwToggle');
    const pwIcon    = document.getElementById('pwToggleIcon');

    pwToggle.addEventListener('click', () => {
        const visible = pwInput.type === 'text';
        pwInput.type  = visible ? 'password' : 'text';
        pwIcon.className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
        pwToggle.setAttribute('aria-pressed', String(!visible));
    });

    // ── Loading state on submit ───────────────────────────────────────────────
    document.getElementById('loginForm').addEventListener('submit', function (e) {
        const email    = document.getElementById('email').value.trim();
        const password = pwInput.value.trim();

        if (!email || !password) return; // let HTML5 handle it

        const btnText    = document.getElementById('btnLoginText');
        const btnSpinner = document.getElementById('btnLoginSpinner');
        const btn        = document.getElementById('btnLogin');

        btnText.classList.add('d-none');
        btnSpinner.classList.remove('d-none');
        btn.disabled = true;
    });

    // ── Auto-dismiss alerts after 6 seconds ──────────────────────────────────
    document.querySelectorAll('.alert-custom').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity .5s, max-height .5s';
            el.style.opacity    = '0';
            el.style.maxHeight  = '0';
            el.style.overflow   = 'hidden';
            el.style.padding    = '0';
            el.style.margin     = '0';
        }, 6000);
    });

    // ── Ethics & Compliance Notice Modal ─────────────────────────────────────
    (function () {
        const STORAGE_KEY = 'acvms_ethics_ack';
        const overlay     = document.getElementById('ethicsOverlay');
        const ackCheckbox = document.getElementById('ethicsAck');
        const acceptBtn   = document.getElementById('ethicsAcceptBtn');

        if (!overlay) return;

        // Show once per browser session (sessionStorage resets on tab close)
        if (!sessionStorage.getItem(STORAGE_KEY)) {
            setTimeout(() => overlay.classList.add('visible'), 800);
        }

        // Enable accept button only when checkbox is checked
        ackCheckbox && ackCheckbox.addEventListener('change', function () {
            if (this.checked) {
                acceptBtn.classList.add('enabled');
                acceptBtn.disabled = false;
            } else {
                acceptBtn.classList.remove('enabled');
                acceptBtn.disabled = true;
            }
        });

        // Dismiss modal
        acceptBtn && acceptBtn.addEventListener('click', function () {
            sessionStorage.setItem(STORAGE_KEY, '1');
            overlay.style.transition = 'opacity .25s ease';
            overlay.style.opacity = '0';
            overlay.style.pointerEvents = 'none';
            setTimeout(() => overlay.remove(), 280);
        });
    })();
</script>

</body>
</html>
