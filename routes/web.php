<?php

/**
 * Web Routes
 *
 * Format:  $router->get('/path',  'ControllerClass@method');
 *          $router->post('/path', 'ControllerClass@method');
 *
 * Route parameters:  /violations/{id}  → passed as argument to the method.
 */

// ── Public routes ────────────────────────────────────────────────────────────
$router->get('/',       'AuthController@showLogin');
$router->get('/login',  'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// ── Dashboard ────────────────────────────────────────────────────────────────
$router->get('/dashboard', 'DashboardController@index');

// ── Violations ───────────────────────────────────────────────────────────────
$router->get('/violations',        'ViolationController@index');
$router->get('/violations/create', 'ViolationController@create');
$router->post('/violations',       'ViolationController@store');
$router->get('/violations/{id}',   'ViolationController@show');
$router->get('/violations/{id}/edit', 'ViolationController@edit');
$router->post('/violations/{id}',  'ViolationController@update');
$router->post('/violations/{id}/delete', 'ViolationController@delete');

// ── Case Management (admin) ───────────────────────────────────────────────────
$router->get('/violations/{id}/review',   'ViolationController@review');
$router->post('/violations/{id}/status',  'ViolationController@updateStatus');
$router->post('/violations/{id}/reject',  'ViolationController@reject');
$router->post('/violations/{id}/close',   'ViolationController@close');
$router->post('/violations/{id}/sanction','ViolationController@assignSanction');

// ── Admin — User Management ──────────────────────────────────────────────────
$router->get('/admin/users',           'UserController@index');
$router->get('/admin/users/create',    'UserController@create');
$router->post('/admin/users',          'UserController@store');
$router->get('/admin/users/{id}',      'UserController@show');
$router->get('/admin/users/{id}/edit', 'UserController@edit');
$router->post('/admin/users/{id}',     'UserController@update');
$router->post('/admin/users/{id}/delete', 'UserController@delete');

// ── Audit Logs ───────────────────────────────────────────────────────────────
$router->get('/admin/audit-logs', 'AuditController@index');

// ── Evidence Files ────────────────────────────────────────────────────────────
$router->post('/violations/{id}/evidence',         'EvidenceController@upload');   // attach file to violation
$router->get('/evidence/{id}',                     'EvidenceController@show');     // serve / download file
$router->post('/evidence/{id}/delete',             'EvidenceController@delete');   // admin hard-delete

// ── Notifications ─────────────────────────────────────────────────────────────
$router->get('/notifications',                     'NotificationController@index');
$router->post('/notifications/read-all',           'NotificationController@markAllRead');
$router->post('/notifications/{id}/read',          'NotificationController@markRead');

