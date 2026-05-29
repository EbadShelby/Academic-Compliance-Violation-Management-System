<?php

/**
 * Dashboard Controller
 *
 * The home screen after login — shows a summary for all roles.
 */

class DashboardController extends Controller
{
    public function index(): void
    {
        // Guard: must be logged in
        AuthMiddleware::handle();

        $user = Session::user();

        // Fetch unread notification count for the dashboard widget
        $unreadCount = 0;
        try {
            if (!class_exists('Notification', false)) {
                require_once BASE_PATH . '/app/models/Notification.php';
            }
            $nm          = new Notification();
            $unreadCount = $nm->getUnreadCount((int) $user['id']);
            $recentNotifications = $nm->getRecentNotifications((int) $user['id'], 5);
        } catch (Throwable $e) {
            $recentNotifications = [];
        }

        $this->view('dashboard.index', [
            'title'               => 'Dashboard — ' . APP_NAME,
            'pageTitle'           => 'Dashboard',
            'user'                => $user,
            'unreadCount'         => $unreadCount,
            'recentNotifications' => $recentNotifications,
        ]);
    }
}
