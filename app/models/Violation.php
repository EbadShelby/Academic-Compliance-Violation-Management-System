<?php

/**
 * Violation Model
 *
 * Handles all database operations for the violations table.
 * The schema uses ENUM columns for severity and status, and a plain
 * VARCHAR for type (category), so no separate lookup tables are needed.
 */

class Violation extends Model
{
    protected string $table = 'violations';

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

    // ── Write operations ─────────────────────────────────────────────────────

    /**
     * Insert a new violation record.
     *
     * Expected $data keys:
     *   student_id, reported_by, type, description,
     *   severity, incident_date
     *
     * Status always starts as 'open'.
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
            'status'        => 'open',
            'incident_date' => $data['incident_date'],
        ];

        return $this->insert($payload);
    }

    // ── Read operations ──────────────────────────────────────────────────────

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
