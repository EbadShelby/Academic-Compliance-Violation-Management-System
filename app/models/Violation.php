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
     * Update an existing violation record.
     *
     * Expected $data keys:
     *   student_id, type, description, severity, incident_date
     *
     * @param  int   $id    Violation ID
     * @param  array $data  Fields to update
     * @return bool         True if successful
     */
    public function updateViolation(int $id, array $data): bool
    {
        $payload = [
            'student_id'    => (int) $data['student_id'],
            'type'          => trim($data['type']),
            'description'   => trim($data['description']),
            'severity'      => $data['severity'],
            'incident_date' => $data['incident_date'],
        ];

        return $this->update($id, $payload);
    }

    /**
     * Hard delete a violation record from the database.
     * (Warning: Ensure associated evidence and logs are handled properly).
     *
     * @param  int  $id Violation ID
     * @return bool     True if deleted
     */
    public function deleteViolation(int $id): bool
    {
        return $this->delete($id);
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

    // ── Dashboard / Analytics ────────────────────────────────────────────────

    /**
     * Total number of violation records in the system.
     */
    public function getTotalViolations(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM `violations`")->fetchColumn();
    }

    /**
     * Number of violations whose status is 'pending'.
     */
    public function getPendingCases(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `violations` WHERE status = 'pending'");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Number of violations whose status is 'resolved' or 'closed'.
     */
    public function getResolvedCases(): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM `violations` WHERE status IN ('resolved','closed')"
        );
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * The violation category (type) with the highest report count.
     *
     * @return string  Category name, or '—' when the table is empty.
     */
    public function getMostCommonCategory(): string
    {
        $stmt = $this->db->prepare(
            "SELECT type, COUNT(*) AS cnt
               FROM `violations`
           GROUP BY type
           ORDER BY cnt DESC
              LIMIT 1"
        );
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? $row['type'] : '—';
    }

    /**
     * Students who have more than one violation (repeat offenders).
     *
     * Returns an array of rows:
     *   student_name, student_number, violation_count, latest_violation
     *
     * @param int $limit  Maximum rows to return (default 10).
     */
    public function getRepeatOffenders(int $limit = 10): array
    {
        $limit = max(1, (int) $limit);
        $sql   = "SELECT
                      CONCAT(u.first_name, ' ', u.last_name) AS student_name,
                      u.student_id                            AS student_number,
                      u.id                                    AS user_id,
                      COUNT(v.id)                             AS violation_count,
                      MAX(v.created_at)                       AS latest_violation
                  FROM `violations` v
                  JOIN `users` u ON u.id = v.student_id
                  GROUP BY v.student_id
                  HAVING COUNT(v.id) > 1
                  ORDER BY violation_count DESC
                  LIMIT {$limit}";
        return $this->query($sql)->fetchAll();
    }

    /**
     * Aggregated violation stats for a specific teacher (reporter).
     *
     * Returns: total, pending, under_review, resolved, rejected, closed
     *
     * @param int $teacherId  user.id of the teacher.
     */
    public function getTeacherViolationStats(int $teacherId): array
    {
        $sql = "SELECT
                    COUNT(*)                                          AS total,
                    SUM(status = 'pending')                          AS pending,
                    SUM(status = 'under_review')                     AS under_review,
                    SUM(status = 'resolved')                         AS resolved,
                    SUM(status = 'rejected')                         AS rejected,
                    SUM(status = 'closed')                           AS closed
                FROM `violations`
                WHERE reported_by = :tid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tid' => $teacherId]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Aggregated violation stats for a specific student (the subject).
     *
     * Returns: total, pending, under_review, resolved, rejected, closed
     *
     * @param int $studentId  user.id of the student.
     */
    public function getStudentViolationStats(int $studentId): array
    {
        $sql = "SELECT
                    COUNT(*)                                          AS total,
                    SUM(status = 'pending')                          AS pending,
                    SUM(status = 'under_review')                     AS under_review,
                    SUM(status = 'resolved')                         AS resolved,
                    SUM(status = 'rejected')                         AS rejected,
                    SUM(status = 'closed')                           AS closed
                FROM `violations`
                WHERE student_id = :sid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sid' => $studentId]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Monthly violation counts for the last N months (system-wide).
     *
     * Returns array of ['month' => 'YYYY-MM', 'total' => n]
     * ordered oldest → newest.
     *
     * @param int $months  Look-back window in months (default 6).
     */
    public function getMonthlyTrend(int $months = 6): array
    {
        $months = max(1, (int) $months);
        $sql    = "SELECT
                       DATE_FORMAT(created_at, '%Y-%m')  AS month,
                       COUNT(*)                           AS total
                   FROM `violations`
                   WHERE created_at >= DATE_SUB(NOW(), INTERVAL :m MONTH)
                   GROUP BY month
                   ORDER BY month ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':m' => $months]);
        return $stmt->fetchAll();
    }

    /**
     * Status distribution for chart (system-wide).
     *
     * Returns array of ['status' => '...', 'total' => n]
     */
    public function getStatusDistribution(): array
    {
        $sql = "SELECT status, COUNT(*) AS total
                FROM `violations`
                GROUP BY status
                ORDER BY FIELD(status,'pending','under_review','resolved','rejected','closed')";
        return $this->query($sql)->fetchAll();
    }

    /**
     * Most recent violations (with student & reporter names).
     *
     * @param int $limit  Number of rows (default 10).
     */
    public function getRecentViolations(int $limit = 10): array
    {
        $limit = max(1, (int) $limit);
        $sql   = "SELECT
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
                  ORDER BY v.created_at DESC
                  LIMIT {$limit}";
        return $this->query($sql)->fetchAll();
    }

    /**
     * Recent violations submitted by a specific teacher.
     *
     * @param int $teacherId
     * @param int $limit
     */
    public function getRecentByTeacher(int $teacherId, int $limit = 10): array
    {
        $limit = max(1, (int) $limit);
        $sql   = "SELECT
                      v.id,
                      v.type,
                      v.severity,
                      v.status,
                      v.incident_date,
                      v.created_at,
                      CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                      s.student_id                            AS student_number
                  FROM `violations` v
                  JOIN `users` s ON s.id = v.student_id
                  WHERE v.reported_by = :tid
                  ORDER BY v.created_at DESC
                  LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tid' => $teacherId]);
        return $stmt->fetchAll();
    }

    /**
     * Category distribution for a specific teacher (for chart).
     *
     * @param int $teacherId
     */
    public function getCategoryDistributionByTeacher(int $teacherId): array
    {
        $stmt = $this->db->prepare(
            "SELECT type AS category, COUNT(*) AS total
               FROM `violations`
              WHERE reported_by = :tid
           GROUP BY type
           ORDER BY total DESC"
        );
        $stmt->execute([':tid' => $teacherId]);
        return $stmt->fetchAll();
    }

    /**
     * Global category distribution (for chart).
     */
    public function getCategoryDistribution(): array
    {
        $sql = "SELECT type AS category, COUNT(*) AS total
                FROM `violations`
                GROUP BY type
                ORDER BY total DESC";
        return $this->query($sql)->fetchAll();
    }

    /**
     * Severity distribution (system-wide).
     */
    public function getSeverityDistribution(): array
    {
        $sql = "SELECT severity, COUNT(*) AS total
                FROM `violations`
                GROUP BY severity
                ORDER BY FIELD(severity,'minor','moderate','major','critical')";
        return $this->query($sql)->fetchAll();
    }

    /**
     * Count of violations currently under_review (admin widget).
     */
    public function getUnderReviewCount(): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM `violations` WHERE status = 'under_review'"
        );
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
