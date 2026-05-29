<?php

/**
 * Front Controller — public/index.php
 *
 * Single entry point for every HTTP request.
 * All traffic is routed here by public/.htaccess.
 */

// ── 1. Define the project root ───────────────────────────────────────────────
// BASE_PATH is the folder that contains app/, routes/, views/, etc.
// public/ sits one level inside it, so we go up one directory.
define('BASE_PATH', dirname(__DIR__));

// ── 2. Error reporting (development vs production) ───────────────────────────
// We load app.php shortly; for now use a safe default.
// After APP_ENV is available, you can tighten this further.
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// ── 3. Load core configuration (defines APP_ENV, APP_URL, etc.) ──────────────
require_once BASE_PATH . '/app/config/app.php';

// Now that APP_ENV is defined we can toggle display_errors safely
if (defined('APP_ENV') && APP_ENV === 'development') {
    ini_set('display_errors', 1);
}

// ── 4. Load core classes ─────────────────────────────────────────────────────
require_once BASE_PATH . '/app/core/Database.php';
require_once BASE_PATH . '/app/core/Session.php';
require_once BASE_PATH . '/app/core/Model.php';
require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/core/Router.php';

// ── 5. Load middleware ───────────────────────────────────────────────────────
require_once BASE_PATH . '/app/middleware/AuthMiddleware.php';
require_once BASE_PATH . '/app/middleware/RoleMiddleware.php';

// ── 6. Load helper files ─────────────────────────────────────────────────────
require_once BASE_PATH . '/app/helpers/auth.php';

// Load additional helpers only if they are non-empty (skip empty stubs)
$helpers = ['redirect.php', 'response.php', 'validation.php'];
foreach ($helpers as $helper) {
    $path = BASE_PATH . '/app/helpers/' . $helper;
    if (file_exists($path) && filesize($path) > 0) {
        require_once $path;
    }
}

// ── 7. Start session ─────────────────────────────────────────────────────────
Session::start();

// ── 8. Boot the router and register routes ───────────────────────────────────
$router = new Router();
require_once BASE_PATH . '/routes/web.php';

// ── 9. Dispatch the request ──────────────────────────────────────────────────
$router->dispatch();
