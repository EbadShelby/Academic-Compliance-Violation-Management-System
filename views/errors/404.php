<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found | <?= defined('APP_NAME') ? htmlspecialchars(APP_NAME) : 'System' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="text-center">
        <h1 class="display-1 fw-bold text-danger">404</h1>
        <p class="fs-4 text-muted">The page you're looking for doesn't exist.</p>
        <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>" class="btn btn-primary mt-3">Go Back Home</a>
    </div>
</body>
</html>
