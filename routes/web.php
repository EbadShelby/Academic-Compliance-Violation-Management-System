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
