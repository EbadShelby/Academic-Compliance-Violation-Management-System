<?php

/**
 * Violation Model
 *
 * Handles all database operations for the violations table.
 * The schema uses ENUM columns for severity and status, and a plain
 * VARCHAR for type (category), so no separate lookup tables are needed.
 *
 * Phase 10 additions:
 *   - VALID_TRANSITIONS — centralized state-machine definition
 *   - isValidTransition()
 *   - updateStatus()
 *   - assignSanction()
 *   - addRejectionReason()
 *   - getViolationById()   (alias of findWithDetails for controller clarity)
 */

class Violation extends Model
{
    protected string $table = 'violations';

    // ── State-machine definition ─────────────────────────────────────────────

    /**
     * Valid status transitions.
     *
     * Key   = current status
     * Value = array of statuses the case may legally move to next
     *
     * Workflow:
     *   pending → under_review → resolved | rejected → closed
     */
    private const VALID_TRANSITIONS = [
        'pending'      => ['under_review'],
        'under_review' => ['resolved', 'rejected'],
        'resolved'     => ['closed'],
        'rejected'     => ['closed'],
        'closed'       => [],           // terminal — no further transitions
    ];

    // ── Static lookup lists (match the schema ENUMs) ─────────────────────────

    /**
     * Return available violation types (categories) as id => label pairs.
     * Stored in the DB as the 'type' VARCHAR column.
     */
    public function getCategories(): array
    {
        return [
            'Cheating'          => 'Cheating',
            'Attendance Fraud'  => 'Attendance Fraud',
            'Misconduct'        => 'Misconduct',
            'Plagiarism'        => 'Plagiarism',
            'Bullying'          => 'Bullying',
            'Vandalism'         => 'Vandalism',
            'Drug Violation'    => 'Drug Violation',
            'Other'             => 'Other',
        ];
    }

    /**
     * Return available severity levels as value => label pairs.
     * Matches the ENUM('minor','moderate','major','critical') in the schema.
     */
    public function getSeverityLevels(): array
    {
        return [
            'minor'    => 'Minor',
            'moderate' => 'Moderate',
            'major'    => 'Major',
            'critical' => 'Critical',
        ];
    }

    /**
     * Return all valid status values in workflow order.
     */
    public function getStatusLabels(): array
    {
        return [
            'pending'      => 'Pending',
            'under_review' => 'Under Review',
            'resolved'     => 'Resolved',
            'rejected'     => 'Rejected',
            'closed'       => 'Closed',
        ];
    }

    // ── State-machine helpers ────────────────────────────────────────────────

    /**
     * Check whether a status transition is allowed.
     *
     * @param  string $current  Current status value
     * @param  string $next     Proposed next status value
     * @return bool
     */
    public function isValidTransition(string $current, string $next): bool
    {
        $allowed = self::VALID_TRANSITIONS[$current] ?? [];
        return in_array($next, $allowed, true);
    }

    /**
     * Return the list of statuses a case can legally move to from its
     * current status. Useful for building contextual UI controls.
     *
     * @param  string $current
     * @return string[]
     */
    public function getAvailableTransitions(string $current): array
    {
        return self::VALID_TRANSITIONS[$current] ?? [];
    }

    // ── Write operations ─────────────────────────────────────────────────────

    /**
     * Insert a new violation record.
     *
     * Expected $data keys:
     *   student_id, reported_by, type, description,
     *   severity, incident_date
     *
     * Status always starts as 'pending'.
     *
     * @param  array    $data
     * @return int      New violation ID
     */
    public function createViolation(array $data): int
    {
        $payload = [
            'student_id'    => (int) $data['student_id'],
            'reported_by'   => (int) $data['reported_by'],
            'type'          => trim($data['type']),
            'description'   => trim($data['description']),
            'severity'      => $data['severity'],
            'status'        => 'pending',
            'incident_date' => $data['incident_date'],
        ];

        return $this->insert($payload);
    }

    /**
     * Update the status of a violation.
     *
     * Transition validity is NOT re-checked here — the controller is
     * responsible for calling isValidTransition() before invoking this.
     *
     * @param  int    $id        Violation ID
     * @param  string $newStatus New status value
     * @return bool              True if a row was affected
     */
    public function updateStatus(int $id, string $newStatus): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE `violations` SET `status` = :status WHERE `id` = :id"
        );
        $stmt->execute([':status' => $newStatus, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Assign or update sanction notes on a violation.
     *
     * @param  int    $id     Violation ID
     * @param  string $notes  Sanction notes text
     * @return bool
     */
    public function assignSanction(int $id, string $notes): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE `violations` SET `sanction_notes` = :notes WHERE `id` = :id"
        );
        $stmt->execute([':notes' => trim($notes), ':id' => $id]);
        return $stmt->rowCount() >= 0; // 0 rows affected if unchanged is still OK
    }

    /**
     * Store a rejection reason on the violation record.
     *
     * @param  int    $id     Violation ID
     * @param  string $reason Rejection reason text
     * @return bool
     */
    public function addRejectionReason(int $id, string $reason): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE `violations` SET `rejection_reason` = :reason WHERE `id` = :id"
        );
        $stmt->execute([':reason' => trim($reason), ':id' => $id]);
        return $stmt->rowCount() >= 0;
    }

    // ── Read operations ──────────────────────────────────────────────────────

    /**
     * Alias for findWithDetails() — provides semantic clarity in controller.
     */
    public function getViolationById(int $id): array|false
    {
        return $this->findWithDetails($id);
    }

    /**
     * Fetch all violations with student name and reporter name.
     */
    public function allWithDetails(string $direction = 'DESC'): array
    {
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT
                    v.id,
                    v.type,
                    v.severity,
                    v.status,
                    v.incident_date,
                    v.created_at,
                    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                    s.student_id                            AS student_number,
                    CONCAT(r.first_name, ' ', r.last_name) AS reporter_name
                FROM `violations` v
                JOIN `users` s ON s.id = v.student_id
                JOIN `users` r ON r.id = v.reported_by
                ORDER BY v.created_at {$direction}";

        return $this->query($sql)->fetchAll();
    }

    /**
     * Fetch a single violation with full detail.
     */
    public function findWithDetails(int $id): array|false
    {
        $sql = "SELECT
                    v.*,
                    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                    s.student_id                            AS student_number,
                    s.email                                 AS student_email,
                    CONCAT(r.first_name, ' ', r.last_name) AS reporter_name,
                    r.email                                 AS reporter_email
                FROM `violations` v
                JOIN `users` s ON s.id = v.student_id
                JOIN `users` r ON r.id = v.reported_by
                WHERE v.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Fetch violations for a specific student (for student self-view).
     */
    public function findByStudent(int $studentUserId, string $direction = 'DESC'): array
    {
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT
                    v.*,
                    CONCAT(r.first_name, ' ', r.last_name) AS reporter_name
                FROM `violations` v
                JOIN `users` r ON r.id = v.reported_by
                WHERE v.student_id = :sid
                ORDER BY v.created_at {$direction}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sid' => $studentUserId]);
        return $stmt->fetchAll();
    }

    /**
     * Count violations filed by a specific reporter.
     */
    public function countByReporter(int $reporterId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM `violations` WHERE reported_by = :rid"
        );
        $stmt->execute([':rid' => $reporterId]);
        return (int) $stmt->fetchColumn();
    }
}
