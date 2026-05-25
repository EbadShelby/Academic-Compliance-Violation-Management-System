<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width:   260px;
            --topbar-height:   64px;
            --brand-primary:   #4f46e5;
            --brand-secondary: #7c3aed;
            --brand-accent:    #06b6d4;
            --surface-dark:    #0f172a;
            --surface-nav:     #111827;
            --surface-card:    #1e293b;
            --surface-hover:   rgba(79,70,229,.12);
            --border-subtle:   rgba(255,255,255,.08);
            --text-primary:    #f8fafc;
            --text-muted:      #94a3b8;
            --text-nav:        #cbd5e1;
        }

        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; font-family: 'Inter', system-ui, sans-serif; background: var(--surface-dark); color: var(--text-primary); }

        /* ── Sidebar ───────────────────────────────────────────────────────── */
        #sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--surface-nav);
            border-right: 1px solid var(--border-subtle);
            display: flex;
            flex-direction: column;
            z-index: 1030;
            transition: transform .3s cubic-bezier(.4,0,.2,1);
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: .875rem;
            padding: 1.25rem 1.25rem 1rem;
            border-bottom: 1px solid var(--border-subtle);
            text-decoration: none;
            color: var(--text-primary);
            flex-shrink: 0;
        }
        .sidebar-brand-icon {
            width: 40px; height: 40px;
            border-radius: .625rem;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            color: #fff;
            flex-shrink: 0;
        }
        .sidebar-brand-text { font-size: .8125rem; font-weight: 700; line-height: 1.25; }
        .sidebar-brand-sub  { font-size: .6875rem; font-weight: 400; color: var(--text-muted); }

        /* Nav sections */
        .nav-section { padding: 1rem 1rem .25rem; }
        .nav-section-label {
            font-size: .625rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--text-muted);
            padding: 0 .25rem;
        }

        /* Nav items */
        .nav-item-custom {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .625rem .875rem;
            border-radius: .625rem;
            color: var(--text-nav);
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
            transition: background .15s, color .15s;
            margin: .125rem 0;
        }
        .nav-item-custom:hover {
            background: var(--surface-hover);
            color: var(--text-primary);
        }
        .nav-item-custom.active {
            background: linear-gradient(90deg, rgba(79,70,229,.25), rgba(79,70,229,.08));
            color: #a5b4fc;
            border-left: 2px solid var(--brand-primary);
            padding-left: calc(.875rem - 2px);
        }
        .nav-item-custom i { font-size: 1.0625rem; flex-shrink: 0; }

        /* Sidebar footer (user card) */
        .sidebar-footer {
            margin-top: auto;
            padding: 1rem;
            border-top: 1px solid var(--border-subtle);
            flex-shrink: 0;
        }
        .user-card {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .75rem;
            border-radius: .75rem;
            background: rgba(255,255,255,.04);
            cursor: default;
        }
        .user-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-accent));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8125rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
            text-transform: uppercase;
        }
        .user-info-name  { font-size: .8125rem; font-weight: 600; line-height: 1.2; }
        .user-info-role  { font-size: .6875rem; color: var(--text-muted); text-transform: capitalize; }

        .btn-logout {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem .875rem;
            border-radius: .5rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: .8125rem;
            font-weight: 500;
            margin-top: .5rem;
            transition: background .15s, color .15s;
        }
        .btn-logout:hover {
            background: rgba(248,113,113,.12);
            color: #f87171;
        }

        /* ── Main content ──────────────────────────────────────────────────── */
        #mainWrap {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left .3s cubic-bezier(.4,0,.2,1);
        }

        /* ── Topbar ────────────────────────────────────────────────────────── */
        #topbar {
            position: sticky;
            top: 0;
            height: var(--topbar-height);
            background: rgba(15,23,42,.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.75rem;
            z-index: 1020;
        }
        .topbar-left  { display: flex; align-items: center; gap: 1rem; }
        .topbar-right { display: flex; align-items: center; gap: .75rem; }

        .page-title {
            font-size: 1.0625rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        /* Sidebar toggle button */
        .sidebar-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 36px; height: 36px;
            background: none;
            border: 1px solid var(--border-subtle);
            border-radius: .5rem;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1.1rem;
            transition: background .15s, color .15s;
        }
        .sidebar-toggle:hover { background: rgba(255,255,255,.06); color: var(--text-primary); }

        /* ── Page content ──────────────────────────────────────────────────── */
        #pageContent { flex: 1; padding: 1.75rem; }

        /* ── Flash messages (auto dismiss) ─────────────────────────────────── */
        .flash-area { margin-bottom: 1.25rem; }
        .flash-item {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            padding: .875rem 1rem;
            border-radius: .75rem;
            font-size: .875rem;
            animation: flashIn .3s ease;
        }
        @keyframes flashIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }
        .flash-success { background: rgba(52,211,153,.12); border: 1px solid rgba(52,211,153,.3); color: #34d399; }
        .flash-error   { background: rgba(248,113,113,.12); border: 1px solid rgba(248,113,113,.3); color: #f87171; }
        .flash-info    { background: rgba(99,102,241,.12);  border: 1px solid rgba(99,102,241,.3);  color: #a5b4fc; }

        /* ── Responsive ────────────────────────────────────────────────────── */
        @media (max-width: 991.98px) {
            #sidebar           { transform: translateX(-100%); }
            #sidebar.open      { transform: translateX(0); }
            #mainWrap          { margin-left: 0; }
            .sidebar-toggle    { display: flex; }
            .sidebar-backdrop  {
                display: none;
                position: fixed; inset: 0;
                background: rgba(0,0,0,.5);
                z-index: 1025;
            }
            .sidebar-backdrop.show { display: block; }
        }
    </style>
</head>
<body>

<!-- Sidebar backdrop (mobile) -->
<div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>

<!-- ══ SIDEBAR ═══════════════════════════════════════════════════════════════ -->
<nav id="sidebar" aria-label="Main navigation">

    <!-- Brand -->
    <a class="sidebar-brand" href="<?= APP_URL ?>/dashboard">
        <div class="sidebar-brand-icon" aria-hidden="true">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <div>
            <div class="sidebar-brand-text">ACVMS</div>
            <div class="sidebar-brand-sub">Compliance System</div>
        </div>
    </a>

    <!-- Main nav -->
    <div class="nav-section">
        <div class="nav-section-label">Main</div>
        <a href="<?= APP_URL ?>/dashboard" class="nav-item-custom <?= (strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false) ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="<?= APP_URL ?>/violations" class="nav-item-custom <?= (strpos($_SERVER['REQUEST_URI'], '/violations') !== false) ? 'active' : '' ?>">
            <i class="bi bi-exclamation-triangle"></i> Violations
        </a>
    </div>

    <!-- Admin nav (admin only) -->
    <?php if (Session::hasRole('admin')): ?>
    <div class="nav-section">
        <div class="nav-section-label">Administration</div>
        <a href="<?= APP_URL ?>/admin/users" class="nav-item-custom <?= (strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false) ? 'active' : '' ?>">
            <i class="bi bi-people"></i> User Management
        </a>
        <a href="<?= APP_URL ?>/admin/audit-logs" class="nav-item-custom <?= (strpos($_SERVER['REQUEST_URI'], '/admin/audit-logs') !== false) ? 'active' : '' ?>">
            <i class="bi bi-journal-text"></i> Audit Logs
        </a>
    </div>
    <?php endif; ?>

    <!-- Teacher nav -->
    <?php if (Session::hasRole('admin') || Session::hasRole('teacher')): ?>
    <div class="nav-section">
        <div class="nav-section-label">Reports</div>
        <a href="<?= APP_URL ?>/violations/create" class="nav-item-custom <?= (strpos($_SERVER['REQUEST_URI'], '/violations/create') !== false) ? 'active' : '' ?>">
            <i class="bi bi-plus-circle"></i> File Violation
        </a>
    </div>
    <?php endif; ?>

    <!-- Sidebar footer -->
    <div class="sidebar-footer">
        <?php $authUser = Session::user(); ?>
        <?php if ($authUser): ?>
        <div class="user-card">
            <div class="user-avatar" aria-hidden="true">
                <?= mb_substr($authUser['name'] ?? 'U', 0, 1) ?>
            </div>
            <div class="flex-grow-1 overflow-hidden">
                <div class="user-info-name text-truncate"><?= htmlspecialchars($authUser['name'] ?? '') ?></div>
                <div class="user-info-role"><?= htmlspecialchars($authUser['role'] ?? '') ?></div>
            </div>
        </div>
        <a href="<?= APP_URL ?>/logout" class="btn-logout" id="logoutLink">
            <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Sign out
        </a>
        <?php endif; ?>
    </div>

</nav>

<!-- ══ MAIN CONTENT WRAPPER ═════════════════════════════════════════════════ -->
<div id="mainWrap">

    <!-- Topbar -->
    <header id="topbar" role="banner">
        <div class="topbar-left">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="sidebar">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>
            <h1 class="page-title"><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></h1>
        </div>
        <div class="topbar-right">
            <?php $authUser = $authUser ?? Session::user(); ?>
            <?php if ($authUser): ?>
            <span class="d-none d-sm-inline text-muted" style="font-size:.8125rem;">
                <?= htmlspecialchars($authUser['name'] ?? '') ?>
            </span>
            <?php endif; ?>
        </div>
    </header>

    <!-- Page Content -->
    <main id="pageContent" role="main">

        <!-- Flash messages -->
        <div class="flash-area" id="flashArea">
            <?php $flashSuccess = Session::getFlash('success'); ?>
            <?php $flashError   = Session::getFlash('error');   ?>
            <?php $flashInfo    = Session::getFlash('info');    ?>

            <?php if ($flashSuccess): ?>
            <div class="flash-item flash-success" role="alert">
                <i class="bi bi-check-circle-fill flex-shrink-0" aria-hidden="true"></i>
                <span><?= htmlspecialchars($flashSuccess) ?></span>
            </div>
            <?php endif; ?>

            <?php if ($flashError): ?>
            <div class="flash-item flash-error" role="alert">
                <i class="bi bi-exclamation-circle-fill flex-shrink-0" aria-hidden="true"></i>
                <span><?= htmlspecialchars($flashError) ?></span>
            </div>
            <?php endif; ?>

            <?php if ($flashInfo): ?>
            <div class="flash-item flash-info" role="alert">
                <i class="bi bi-info-circle-fill flex-shrink-0" aria-hidden="true"></i>
                <span><?= htmlspecialchars($flashInfo) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- View content injected here -->
        <?= $content ?>

    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ── Mobile sidebar toggle ─────────────────────────────────────────────────
    const sidebar   = document.getElementById('sidebar');
    const backdrop  = document.getElementById('sidebarBackdrop');
    const toggleBtn = document.getElementById('sidebarToggle');

    function openSidebar() {
        sidebar.classList.add('open');
        backdrop.classList.add('show');
        toggleBtn && toggleBtn.setAttribute('aria-expanded', 'true');
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        backdrop.classList.remove('show');
        toggleBtn && toggleBtn.setAttribute('aria-expanded', 'false');
    }

    toggleBtn  && toggleBtn.addEventListener('click',  openSidebar);
    backdrop   && backdrop.addEventListener('click',   closeSidebar);

    // Close on Escape
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });

    // ── Auto-dismiss flash messages ───────────────────────────────────────────
    document.querySelectorAll('.flash-item').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity .5s, max-height .5s, margin .5s, padding .5s';
            el.style.opacity    = '0';
            el.style.maxHeight  = '0';
            el.style.overflow   = 'hidden';
            el.style.padding    = '0';
            el.style.margin     = '0';
        }, 5000);
    });

    // ── Logout confirmation ───────────────────────────────────────────────────
    const logoutLink = document.getElementById('logoutLink');
    if (logoutLink) {
        logoutLink.addEventListener('click', e => {
            if (!confirm('Are you sure you want to sign out?')) {
                e.preventDefault();
            }
        });
    }
</script>

</body>
</html>
