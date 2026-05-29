<?php

/**
 * Notification Controller
 *
 * Handles the notification inbox and read-state actions.
 *
 * Routes:
 *   GET  /notifications           → index()        — full notification list
 *   POST /notifications/{id}/read → markRead($id)  — mark one read
 *   POST /notifications/read-all  → markAllRead()  — mark all read
 */

class NotificationController extends Controller
{
    private function notificationModel(): Notification
    {
        /** @var Notification */
        return $this->model('Notification');
    }

    // =========================================================================
    // GET /notifications
    // =========================================================================

    public function index(): void
    {
        AuthMiddleware::handle();

        $authUser = Session::user();
        $nm       = $this->notificationModel();

        $notifications = $nm->getUserNotifications((int) $authUser['id']);
        $unreadCount   = $nm->getUnreadCount((int) $authUser['id']);

        $this->view('notifications.index', [
            'title'         => 'Notifications — ' . APP_NAME,
            'pageTitle'     => 'Notifications',
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }

    // =========================================================================
    // POST /notifications/{id}/read
    // =========================================================================

    public function markRead(int $id): void
    {
        AuthMiddleware::handle();

        $authUser = Session::user();
        $nm       = $this->notificationModel();

        // Ownership is enforced inside markAsRead() — only the owning user's
        // row will be updated; no row affected = silent no-op.
        $nm->markAsRead($id, (int) $authUser['id']);

        // JSON response if the client sent an AJAX request
        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        $this->redirect(APP_URL . '/notifications');
    }

    // =========================================================================
    // POST /notifications/read-all
    // =========================================================================

    public function markAllRead(): void
    {
        AuthMiddleware::handle();

        $authUser = Session::user();
        $nm       = $this->notificationModel();

        $nm->markAllAsRead((int) $authUser['id']);

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        Session::flash('success', 'All notifications marked as read.');
        $this->redirect(APP_URL . '/notifications');
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Detect whether the current request is an XMLHttpRequest.
     */
    private function isAjax(): bool
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }
}
