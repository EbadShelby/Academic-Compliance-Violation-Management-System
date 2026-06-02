<?php

/**
 * Notification Service
 *
 * Central hub for all notification creation logic.
 * Controllers should call methods here — never insert notifications directly.
 *
 * All methods are fire-and-forget: failures are logged but never bubble up.
 *
 * Usage (inside any controller or after a model action):
 *   $ns = new NotificationService();
 *   $ns->notifyStudentViolationFiled($studentId, $violationId);
 *   $ns->notifyAdminNewViolation($violationId, $reporterName);
 */

class NotificationService
{
    private Notification $model;

    public function __construct()
    {
        // Ensure the model class is loaded
        if (!class_exists('Notification', false)) {
            require_once BASE_PATH . '/app/models/Notification.php';
        }
        $this->model = new Notification();
    }

    // =========================================================================
    // STUDENT NOTIFICATIONS
    // =========================================================================

    /**
     * Notify a student that a violation has been filed against them.
     *
     * @param int $studentId
     * @param int $violationId
     */
    public function notifyStudentViolationFiled(int $studentId, int $violationId): void
    {
        $this->send($studentId, [
            'title'           => 'Violation Report Filed',
            'message'         => 'A violation report has been submitted against you. Please review case #' . $violationId . ' for details.',
            'type'            => 'warning',
            'reference_id'    => $violationId,
            'reference_table' => 'violations',
        ]);
    }

    /**
     * Notify a student when their case status has been updated.
     *
     * @param int    $studentId
     * @param int    $violationId
     * @param string $newStatus   Human-readable status label
     */
    public function notifyStudentStatusUpdated(int $studentId, int $violationId, string $newStatus): void
    {
        $this->send($studentId, [
            'title'           => 'Case Status Updated',
            'message'         => 'Your violation case #' . $violationId . ' has been updated to: ' . $newStatus . '.',
            'type'            => 'info',
            'reference_id'    => $violationId,
            'reference_table' => 'violations',
        ]);
    }

    /**
     * Notify a student when their case is resolved.
     *
     * @param int $studentId
     * @param int $violationId
     */
    public function notifyStudentCaseResolved(int $studentId, int $violationId): void
    {
        $this->send($studentId, [
            'title'           => 'Case Resolved',
            'message'         => 'Your violation case #' . $violationId . ' has been marked as resolved.',
            'type'            => 'success',
            'reference_id'    => $violationId,
            'reference_table' => 'violations',
        ]);
    }

    /**
     * Notify a student when their case is rejected.
     *
     * @param int $studentId
     * @param int $violationId
     */
    public function notifyStudentCaseRejected(int $studentId, int $violationId): void
    {
        $this->send($studentId, [
            'title'           => 'Case Rejected',
            'message'         => 'Your violation case #' . $violationId . ' has been rejected by the administrator.',
            'type'            => 'danger',
            'reference_id'    => $violationId,
            'reference_table' => 'violations',
        ]);
    }

    /**
     * Notify a student when their case is closed.
     *
     * @param int $studentId
     * @param int $violationId
     */
    public function notifyStudentCaseClosed(int $studentId, int $violationId): void
    {
        $this->send($studentId, [
            'title'           => 'Case Closed',
            'message'         => 'Your violation case #' . $violationId . ' has been officially closed.',
            'type'            => 'info',
            'reference_id'    => $violationId,
            'reference_table' => 'violations',
        ]);
    }

    // =========================================================================
    // ADMIN NOTIFICATIONS
    // =========================================================================

    /**
     * Notify all admins that a new violation has been submitted.
     *
     * @param int    $violationId
     * @param string $reporterName  Name of the teacher who filed the report
     */
    public function notifyAdminsNewViolation(int $violationId, string $reporterName): void
    {
        $adminIds = $this->getAdminIds();
        foreach ($adminIds as $adminId) {
            $this->send($adminId, [
                'title'           => 'New Violation Report',
                'message'         => $reporterName . ' filed a new violation report (Case #' . $violationId . ') that requires your review.',
                'type'            => 'warning',
                'reference_id'    => $violationId,
                'reference_table' => 'violations',
            ]);
        }
    }

    /**
     * Notify all admins that evidence has been uploaded for a violation.
     *
     * @param int    $violationId
     * @param string $uploaderName
     */
    public function notifyAdminsEvidenceUploaded(int $violationId, string $uploaderName): void
    {
        $adminIds = $this->getAdminIds();
        foreach ($adminIds as $adminId) {
            $this->send($adminId, [
                'title'           => 'Evidence Uploaded',
                'message'         => $uploaderName . ' uploaded new evidence for case #' . $violationId . '.',
                'type'            => 'info',
                'reference_id'    => $violationId,
                'reference_table' => 'violations',
            ]);
        }
    }

    // =========================================================================
    // TEACHER NOTIFICATIONS
    // =========================================================================

    /**
     * Notify the reporting teacher that their submitted case has been reviewed
     * (status updated by admin).
     *
     * @param int    $teacherId
     * @param int    $violationId
     * @param string $newStatus   Human-readable label
     */
    public function notifyTeacherCaseReviewed(int $teacherId, int $violationId, string $newStatus): void
    {
        $this->send($teacherId, [
            'title'           => 'Case Status Updated',
            'message'         => 'Your submitted violation case #' . $violationId . ' has been updated to: ' . $newStatus . '.',
            'type'            => 'info',
            'reference_id'    => $violationId,
            'reference_table' => 'violations',
        ]);
    }

    /**
     * Notify the reporting teacher that their case has been rejected.
     *
     * @param int    $teacherId
     * @param int    $violationId
     * @param string $reason
     */
    public function notifyTeacherCaseRejected(int $teacherId, int $violationId, string $reason): void
    {
        $this->send($teacherId, [
            'title'           => 'Case Rejected',
            'message'         => 'Your submitted violation case #' . $violationId . ' was rejected. Reason: ' . $reason,
            'type'            => 'danger',
            'reference_id'    => $violationId,
            'reference_table' => 'violations',
        ]);
    }

    /**
     * Notify the reporting teacher that a sanction has been assigned.
     *
     * @param int $teacherId
     * @param int $violationId
     */
    public function notifyTeacherSanctionAssigned(int $teacherId, int $violationId): void
    {
        $this->send($teacherId, [
            'title'           => 'Sanction Assigned',
            'message'         => 'A sanction has been assigned to violation case #' . $violationId . ' that you reported.',
            'type'            => 'success',
            'reference_id'    => $violationId,
            'reference_table' => 'violations',
        ]);
    }

    /**
     * Notify the reporting teacher that their submitted case has been closed.
     *
     * @param int $teacherId
     * @param int $violationId
     */
    public function notifyTeacherCaseClosed(int $teacherId, int $violationId): void
    {
        $this->send($teacherId, [
            'title'           => 'Case Closed',
            'message'         => 'Your submitted violation case #' . $violationId . ' has been officially closed.',
            'type'            => 'info',
            'reference_id'    => $violationId,
            'reference_table' => 'violations',
        ]);
    }

    /**
     * Notify all admins that a student submitted a defense or appeal.
     *
     * @param int    $violationId
     * @param string $studentName
     */
    public function notifyAdminsAppealSubmitted(int $violationId, string $studentName): void
    {
        $adminIds = $this->getAdminIds();
        foreach ($adminIds as $adminId) {
            $this->send($adminId, [
                'title'           => 'Student Appeal Submitted',
                'message'         => $studentName . ' has submitted a formal defense/appeal for Case #' . $violationId . '.',
                'type'            => 'warning',
                'reference_id'    => $violationId,
                'reference_table' => 'violations',
            ]);
        }
    }

    /**
     * Notify the reporting teacher that a student submitted an appeal/defense.
     *
     * @param int    $teacherId
     * @param int    $violationId
     * @param string $studentName
     */
    public function notifyTeacherAppealSubmitted(int $teacherId, int $violationId, string $studentName): void
    {
        $this->send($teacherId, [
            'title'           => 'Student Appeal Submitted',
            'message'         => $studentName . ' has submitted a defense/appeal for your reported Case #' . $violationId . '.',
            'type'            => 'info',
            'reference_id'    => $violationId,
            'reference_table' => 'violations',
        ]);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Fire-and-forget notification insert.
     * Failures are logged silently — they must never crash the calling action.
     *
     * @param int   $userId
     * @param array $data
     */
    private function send(int $userId, array $data): void
    {
        try {
            $data['user_id'] = $userId;
            $this->model->createNotification($data);
        } catch (Throwable $e) {
            error_log('ACVMS NotificationService::send — ' . $e->getMessage());
        }
    }

    /**
     * Fetch all active admin user IDs from the database.
     *
     * @return int[]
     */
    private function getAdminIds(): array
    {
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare(
                "SELECT u.id FROM users u
                   JOIN roles r ON u.role_id = r.id
                  WHERE r.slug IN ('admin', 'registrar')
                    AND u.is_active = 1"
            );
            $stmt->execute();
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');
        } catch (Throwable $e) {
            error_log('ACVMS NotificationService::getAdminIds — ' . $e->getMessage());
            return [];
        }
    }
}
