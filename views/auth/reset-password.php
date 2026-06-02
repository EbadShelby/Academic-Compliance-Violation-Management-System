<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Set a new password for the Academic Compliance & Violation Management System.">
    <title><?= htmlspecialchars($title ?? 'Reset Password — ' . APP_NAME) ?></title>

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
            line-height: 1.5;
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
            margin-bottom: 1.75rem;
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

        /* ── Back to login ────────────────────────────────────────────────── */
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            font-size: .875rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: color .2s;
        }
        .back-link:hover {
            color: var(--text-primary);
        }

        /* ── Responsive ───────────────────────────────────────────────────── */
        @media (max-width: 480px) {
            .login-card { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

<div class="login-bg">
    <!-- Decorative orbs -->
    <div class="orb orb-1" aria-hidden="true"></div>
    <div class="orb orb-2" aria-hidden="true"></div>
    <div class="orb orb-3" aria-hidden="true"></div>

    <div class="login-card">

        <!-- Brand -->
        <div class="brand-icon" aria-hidden="true">
            <i class="bi bi-shield-lock"></i>
        </div>

        <h1 class="login-title">Reset Password</h1>
        <p class="login-subtitle">Please create a new, strong password for your account.</p>

        <!-- Error alert -->
        <?php if (!empty($error)): ?>
        <div class="alert-custom alert-error" role="alert">
            <i class="bi bi-exclamation-circle-fill flex-shrink-0" style="margin-top:1px;"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <!-- Reset Password Form -->
        <form id="resetPasswordForm" method="POST" action="<?= APP_URL ?>/reset-password" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '') ?>">

            <!-- New Password -->
            <div>
                <label for="password" class="form-label-custom">New Password</label>
                <div class="input-wrapper">
                    <i class="bi bi-lock input-icon" aria-hidden="true"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control-custom"
                        placeholder="Min 8 characters"
                        required
                        autofocus
                        minlength="8"
                    >
                    <button type="button" class="pw-toggle" id="pwToggle1" aria-label="Toggle password visibility">
                        <i class="bi bi-eye" id="pwToggleIcon1" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="form-label-custom">Confirm Password</label>
                <div class="input-wrapper">
                    <i class="bi bi-lock-fill input-icon" aria-hidden="true"></i>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control-custom"
                        placeholder="Re-enter password"
                        required
                        minlength="8"
                    >
                    <button type="button" class="pw-toggle" id="pwToggle2" aria-label="Toggle password visibility">
                        <i class="bi bi-eye" id="pwToggleIcon2" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-login" id="btnSubmit">
                <span id="btnSubmitText">
                    <i class="bi bi-check2-circle me-2" aria-hidden="true"></i>Reset Password
                </span>
                <span id="btnSubmitSpinner" class="d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Saving…
                </span>
            </button>

        </form>

    </div><!-- /.login-card -->
</div><!-- /.login-bg -->

<!-- Bootstrap JS bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // ── Password visibility toggle ────────────────────────────────────────────
    function setupToggle(inputId, toggleId, iconId) {
        const input = document.getElementById(inputId);
        const toggle = document.getElementById(toggleId);
        const icon = document.getElementById(iconId);

        toggle.addEventListener('click', () => {
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            icon.className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
            toggle.setAttribute('aria-pressed', String(!visible));
        });
    }

    setupToggle('password', 'pwToggle1', 'pwToggleIcon1');
    setupToggle('password_confirmation', 'pwToggle2', 'pwToggleIcon2');

    // ── Loading state on submit ───────────────────────────────────────────────
    document.getElementById('resetPasswordForm').addEventListener('submit', function (e) {
        const p1 = document.getElementById('password').value.trim();
        const p2 = document.getElementById('password_confirmation').value.trim();

        if (!p1 || !p2) return; // Let HTML5 validation handle empty fields

        if (p1 !== p2) {
            e.preventDefault();
            alert("Passwords do not match.");
            return;
        }

        const btnText    = document.getElementById('btnSubmitText');
        const btnSpinner = document.getElementById('btnSubmitSpinner');
        const btn        = document.getElementById('btnSubmit');

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
</script>

</body>
</html>
