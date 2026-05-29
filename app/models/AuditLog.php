<?php

/**
 * AuditLog Model
 *
 * Writes immutable audit trail entries into the audit_logs table.
 *
 * Schema columns:
 *   id, user_id, action, target_type, target_id,
 *   detail (JSON), ip_address, user_agent, created_at
 */

class AuditLog extends Model
{
    protected string $table = 'audit_logs';

    /**
     * Create an audit log entry.
     *
     * Expected $data keys:
     *   user_id      (int|null)    — null for system actions
     *   action       (string)      — e.g. 'violation.created'
     *   target_type  (string|null) — e.g. 'Violation'
     *   target_id    (int|null)    — PK of the affected record
     *   detail       (array|null)  — arbitrary context, stored as JSON
     *   ip_address   (string|null)
     *   user_agent   (string|null)
     *
     * @param  array $data
     * @return int   New log ID
     */
    public function createLog(array $data): int
    {
        $payload = [
            'user_id'     => isset($data['user_id'])    ? (int) $data['user_id']    : null,
            'action'      => $data['action'],
            'target_type' => $data['target_type'] ?? null,
            'target_id'   => isset($data['target_id'])  ? (int) $data['target_id']  : null,
            'detail'      => isset($data['detail'])     ? json_encode($data['detail']) : null,
            'ip_address'  => $data['ip_address']  ?? null,
            'user_agent'  => $data['user_agent']  ?? null,
        ];

        return $this->insert($payload);
    }

    /**
     * Fetch recent audit log entries (for admin log viewer).
     *
     * @param  int   $limit
     * @return array
     */
    public function recent(int $limit = 100): array
    {
        $sql = "SELECT al.*,
                       CONCAT(u.first_name, ' ', u.last_name) AS actor_name,
                       u.email                                  AS actor_email
                FROM `audit_logs` al
                LEFT JOIN `users` u ON u.id = al.user_id
                ORDER BY al.created_at DESC
                LIMIT :lim";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
