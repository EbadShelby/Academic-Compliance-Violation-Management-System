<?php

/**
 * Front Controller — Entry Point
 *
 * All requests are routed through this file via .htaccess.
 *
 * Bootstrap order:
 *  1. App config  (constants, env loader)
 *  2. Core classes (Database, Model, Controller, Router, Session)
 *  3. Session start
 *  4. Route dispatch
 */

// ── 1. App configuration & env loader ───────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/config/app.php';

// ── 2. Core classes ──────────────────────────────────────────────────────────
require_once BASE_PATH . '/app/core/Database.php';
require_once BASE_PATH . '/app/core/Model.php';
require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/core/Session.php';
require_once BASE_PATH . '/app/core/Router.php';

// ── 3. Start session ─────────────────────────────────────────────────────────
Session::start();

// ── 4. Build router and load routes ─────────────────────────────────────────
$router = new Router();
require_once BASE_PATH . '/routes/web.php';

// ── 5. Dispatch ──────────────────────────────────────────────────────────────
$router->dispatch();
