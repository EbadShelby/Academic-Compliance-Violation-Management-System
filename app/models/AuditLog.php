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

    // ── Pagination default ────────────────────────────────────────────────────
    public const PER_PAGE = 50;

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

    // =========================================================================
    // Read methods
    // =========================================================================

    /**
     * Fetch all audit log entries, newest first.
     *
     * @return array
     */
    public function getAllLogs(): array
    {
        return $this->recent(PHP_INT_MAX);
    }

    /**
     * Count failed login attempts for a specific IP within the given timeframe.
     *
     * @param string $ip
     * @param int $minutes
     * @return int
     */
    public function countFailedLoginsByIp(string $ip, int $minutes = 5): int
    {
        $sql = "SELECT COUNT(*) FROM `audit_logs`
                WHERE action = 'auth.login_failed'
                  AND ip_address = :ip
                  AND created_at >= DATE_SUB(NOW(), INTERVAL :mins MINUTE)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
        $stmt->bindValue(':mins', $minutes, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Fetch all audit log entries for a specific user.
     *
     * @param  int   $userId
     * @return array
     */
    public function getLogsByUser(int $userId): array
    {
        $sql = "SELECT al.*,
                       CONCAT(u.first_name, ' ', u.last_name) AS actor_name,
                       u.email                                  AS actor_email
                FROM `audit_logs` al
                LEFT JOIN `users` u ON u.id = al.user_id
                WHERE al.user_id = :uid
                ORDER BY al.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Fetch all audit log entries that touch a specific table / target type.
     *
     * @param  string $table  e.g. 'violations', 'users'
     * @return array
     */
    public function getLogsByTable(string $table): array
    {
        $sql = "SELECT al.*,
                       CONCAT(u.first_name, ' ', u.last_name) AS actor_name,
                       u.email                                  AS actor_email
                FROM `audit_logs` al
                LEFT JOIN `users` u ON u.id = al.user_id
                WHERE al.target_type = :ttype
                ORDER BY al.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ttype', $table, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Filterable, paginated log query for the admin viewer.
     *
     * Filters (all optional):
     *   search     — free-text match against action, actor name, or IP
     *   user_id    — filter by actor
     *   action     — exact action key prefix (LIKE 'xxx%')
     *   date_from  — YYYY-MM-DD lower bound (inclusive)
     *   date_to    — YYYY-MM-DD upper bound (inclusive)
     *
     * @param  array $filters
     * @param  int   $page     1-based page number
     * @param  int   $perPage
     * @return array{rows: array, total: int, pages: int}
     */
    public function getLogs(array $filters = [], int $page = 1, int $perPage = self::PER_PAGE): array
    {
        [$where, $params] = $this->buildWhere($filters);

        // Count total
        $countSql = "SELECT COUNT(*) FROM `audit_logs` al
                     LEFT JOIN `users` u ON u.id = al.user_id
                     $where";
        $cStmt = $this->db->prepare($countSql);
        $cStmt->execute($params);
        $total = (int) $cStmt->fetchColumn();

        $pages  = $perPage > 0 ? (int) ceil($total / $perPage) : 1;
        $page   = max(1, min($page, $pages ?: 1));
        $offset = ($page - 1) * $perPage;

        $rowSql = "SELECT al.*,
                          CONCAT(u.first_name, ' ', u.last_name) AS actor_name,
                          u.email                                  AS actor_email
                   FROM `audit_logs` al
                   LEFT JOIN `users` u ON u.id = al.user_id
                   $where
                   ORDER BY al.created_at DESC
                   LIMIT :lim OFFSET :off";

        $rStmt = $this->db->prepare($rowSql);
        foreach ($params as $key => $value) {
            $rStmt->bindValue($key, $value);
        }
        $rStmt->bindValue(':lim',  $perPage, PDO::PARAM_INT);
        $rStmt->bindValue(':off',  $offset,  PDO::PARAM_INT);
        $rStmt->execute();

        return [
            'rows'  => $rStmt->fetchAll(),
            'total' => $total,
            'pages' => $pages,
            'page'  => $page,
        ];
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

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Build WHERE clause + named parameter map from the filter array.
     *
     * @param  array $filters
     * @return array{0: string, 1: array}
     */
    private function buildWhere(array $filters): array
    {
        $clauses = [];
        $params  = [];

        if (!empty($filters['user_id'])) {
            $clauses[] = 'al.user_id = :fuid';
            $params[':fuid'] = (int) $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $clauses[] = 'al.action LIKE :faction';
            $params[':faction'] = $filters['action'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $clauses[] = 'DATE(al.created_at) >= :fdfrom';
            $params[':fdfrom'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $clauses[] = 'DATE(al.created_at) <= :fdto';
            $params[':fdto'] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $like = '%' . $filters['search'] . '%';
            $clauses[] = '(al.action LIKE :fsearch1
                           OR CONCAT(u.first_name, " ", u.last_name) LIKE :fsearch2
                           OR u.email LIKE :fsearch3
                           OR al.ip_address LIKE :fsearch4)';
            $params[':fsearch1'] = $like;
            $params[':fsearch2'] = $like;
            $params[':fsearch3'] = $like;
            $params[':fsearch4'] = $like;
        }

        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';
        return [$where, $params];
    }
}
