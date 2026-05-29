<?php

/**
 * Dashboard Controller — Phase 13
 *
 * Routes each authenticated role to its own dashboard view,
 * pre-loading all analytics data from the Violation model.
 */

class DashboardController extends Controller
{
    // ── Entry point ───────────────────────────────────────────────────────────

    public function index(): void
    {
        AuthMiddleware::handle();

        $user = Session::user();
        $role = strtolower($user['role'] ?? 'student');

        switch ($role) {
            case 'admin':
                $this->adminDashboard($user);
                break;
            case 'teacher':
                $this->teacherDashboard($user);
                break;
            default:
                $this->studentDashboard($user);
        }
    }

    // ── Role dashboards ───────────────────────────────────────────────────────

    /**
     * Admin dashboard — full system view.
     */
    private function adminDashboard(array $user): void
    {
        $vm  = $this->loadViolationModel();
        $nm  = $this->loadNotificationModel();
        $um  = $this->loadUserModel();

        $totalViolations   = $vm->getTotalViolations();
        $pendingCases      = $vm->getPendingCases();
        $resolvedCases     = $vm->getResolvedCases();
        $underReview       = $vm->getUnderReviewCount();
        $mostCommonCat     = $vm->getMostCommonCategory();
        $repeatOffenders   = $vm->getRepeatOffenders(5);
        $recentViolations  = $vm->getRecentViolations(10);
        $statusDist        = $vm->getStatusDistribution();
        $categoryDist      = $vm->getCategoryDistribution();
        $severityDist      = $vm->getSeverityDistribution();
        $monthlyTrend      = $vm->getMonthlyTrend(6);
        $userCounts        = $um->countByRole();
        $unreadCount       = $nm ? $nm->getUnreadCount((int) $user['id']) : 0;
        $recentNotifications = $nm ? $nm->getRecentNotifications((int) $user['id'], 5) : [];

        $this->view('dashboard.admin', [
            'title'               => 'Admin Dashboard — ' . APP_NAME,
            'pageTitle'           => 'Admin Dashboard',
            'user'                => $user,
            'totalViolations'     => $totalViolations,
            'pendingCases'        => $pendingCases,
            'resolvedCases'       => $resolvedCases,
            'underReview'         => $underReview,
            'mostCommonCat'       => $mostCommonCat,
            'repeatOffenders'     => $repeatOffenders,
            'recentViolations'    => $recentViolations,
            'statusDist'          => $statusDist,
            'categoryDist'        => $categoryDist,
            'severityDist'        => $severityDist,
            'monthlyTrend'        => $monthlyTrend,
            'userCounts'          => $userCounts,
            'unreadCount'         => $unreadCount,
            'recentNotifications' => $recentNotifications,
        ]);
    }

    /**
     * Teacher dashboard — scoped to reports filed by this teacher.
     */
    private function teacherDashboard(array $user): void
    {
        $vm  = $this->loadViolationModel();
        $nm  = $this->loadNotificationModel();
        $tid = (int) $user['id'];

        $stats               = $vm->getTeacherViolationStats($tid);
        $recentViolations    = $vm->getRecentByTeacher($tid, 10);
        $categoryDist        = $vm->getCategoryDistributionByTeacher($tid);
        $unreadCount         = $nm ? $nm->getUnreadCount($tid) : 0;
        $recentNotifications = $nm ? $nm->getRecentNotifications($tid, 5) : [];

        $this->view('dashboard.teacher', [
            'title'               => 'Teacher Dashboard — ' . APP_NAME,
            'pageTitle'           => 'My Dashboard',
            'user'                => $user,
            'stats'               => $stats,
            'recentViolations'    => $recentViolations,
            'categoryDist'        => $categoryDist,
            'unreadCount'         => $unreadCount,
            'recentNotifications' => $recentNotifications,
        ]);
    }

    /**
     * Student dashboard — scoped to this student's own violations.
     */
    private function studentDashboard(array $user): void
    {
        $vm  = $this->loadViolationModel();
        $nm  = $this->loadNotificationModel();
        $sid = (int) $user['id'];

        $stats               = $vm->getStudentViolationStats($sid);
        $violations          = $vm->findByStudent($sid);
        $unreadCount         = $nm ? $nm->getUnreadCount($sid) : 0;
        $recentNotifications = $nm ? $nm->getRecentNotifications($sid, 5) : [];

        $this->view('dashboard.student', [
            'title'               => 'My Dashboard — ' . APP_NAME,
            'pageTitle'           => 'My Dashboard',
            'user'                => $user,
            'stats'               => $stats,
            'violations'          => $violations,
            'unreadCount'         => $unreadCount,
            'recentNotifications' => $recentNotifications,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function loadViolationModel(): Violation
    {
        if (!class_exists('Violation', false)) {
            require_once BASE_PATH . '/app/models/Violation.php';
        }
        return new Violation();
    }

    private function loadNotificationModel(): ?Notification
    {
        try {
            if (!class_exists('Notification', false)) {
                require_once BASE_PATH . '/app/models/Notification.php';
            }
            return new Notification();
        } catch (Throwable $e) {
            return null;
        }
    }

    private function loadUserModel(): User
    {
        if (!class_exists('User', false)) {
            require_once BASE_PATH . '/app/models/User.php';
        }
        return new User();
    }
}
