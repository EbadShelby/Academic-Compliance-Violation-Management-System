<?php

/**
 * Centralized Audit Logger Helper
 *
 * Provides a single reusable function to write audit trail entries.
 * All controllers should call logAction() instead of instantiating
 * AuditLog directly or writing raw SQL.
 *
 * Usage:
 *   logAction('violation.created', 'Violation', $id);
 *   logAction('user.login',        'User',       $userId, ['email' => $email]);
 *   logAction('user.login_failed', null,          null,    ['email' => $email]);
 *
 * The user_id, ip_address, and user_agent are captured automatically
 * from the active session and server globals.
 *
 * Failures are silently swallowed and written to the PHP error log so
 * that a logging hiccup never breaks the main application flow.
 */

/**
 * Record an audit event.
 *
 * @param  string      $action      Dot-notation event key, e.g. 'user.login'
 * @param  string|null $targetType  Model/entity class name, e.g. 'User', 'Violation'
 * @param  int|null    $targetId    Primary key of the affected record
 * @param  array|null  $detail      Arbitrary context stored as JSON
 * @param  int|null    $userId      Override the session user; null = auto-detect
 * @return void
 */
function logAction(
    string  $action,
    ?string $targetType = null,
    ?int    $targetId   = null,
    ?array  $detail     = null,
    ?int    $userId     = null
): void {
    try {
        // Resolve the acting user from the session if not explicitly given
        if ($userId === null) {
            $sessionUser = Session::user();
            $userId      = $sessionUser ? (int) $sessionUser['id'] : null;
        }

        // Load the model file and instantiate directly —
        // we cannot use Controller::model() here because Controller is abstract.
        $modelPath = BASE_PATH . '/app/models/AuditLog.php';
        if (!class_exists('AuditLog', false)) {
            require_once $modelPath;
        }

        /** @var AuditLog $log */
        $log = new AuditLog();

        $log->createLog([
            'user_id'     => $userId,
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'detail'      => $detail,
            'ip_address'  => $_SERVER['REMOTE_ADDR']     ?? null,
            'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

    } catch (Throwable $e) {
        // Never let a logging failure crash the application
        error_log('ACVMS logAction() failed — ' . $e->getMessage());
    }
}
