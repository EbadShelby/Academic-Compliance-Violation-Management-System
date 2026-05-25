<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Forbidden | <?= defined('APP_NAME') ? htmlspecialchars(APP_NAME) : 'System' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
        }
        .error-code {
            font-size: clamp(5rem, 20vw, 8rem);
            font-weight: 700;
            line-height: 1;
            background: linear-gradient(135deg, #f87171, #ef4444);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.04em;
        }
        .error-icon {
            font-size: 3rem;
            color: #f87171;
            margin-bottom: 1rem;
            display: block;
        }
        .error-title  { font-size: 1.5rem; font-weight: 700; margin: .75rem 0 .5rem; }
        .error-desc   { color: #94a3b8; font-size: .9375rem; max-width: 400px; margin: 0 auto 2rem; }
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .75rem 1.75rem;
            border-radius: .75rem;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: .9375rem;
            transition: opacity .2s, transform .15s;
            box-shadow: 0 4px 20px rgba(79,70,229,.4);
        }
        .btn-home:hover { opacity: .9; transform: translateY(-1px); color: #fff; }
    </style>
</head>
<body>
    <div>
        <i class="bi bi-shield-x error-icon" aria-hidden="true"></i>
        <div class="error-code" aria-hidden="true">403</div>
        <h1 class="error-title">Access Forbidden</h1>
        <p class="error-desc">
            You don't have permission to view this page.<br>
            Contact an administrator if you believe this is an error.
        </p>
        <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>/dashboard" class="btn-home">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            Back to Dashboard
        </a>
    </div>
</body>
</html>
