<?php

/**
 * User Model
 *
 * Handles all database operations related to the users table.
 */

class User extends Model
{
    protected string $table = 'users';

    // ── Auth helpers ─────────────────────────────────────────────────────────

    /**
     * Find a user record by their email address.
     *
     * Joins the roles table so the role slug is available immediately.
     *
     * @param  string      $email
     * @return array|false  Row array or false when not found.
     */
    public function findByEmail(string $email): array|false
    {
        $sql = "SELECT u.*, r.slug AS role
                FROM `users` u
                LEFT JOIN `roles` r ON r.id = u.role_id
                WHERE u.email = :email
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Verify a plain-text password against a bcrypt hash.
     *
     * @param  string $plainPassword  The password submitted by the user.
     * @param  string $hash           The hash stored in the database.
     * @return bool
     */
    public function verifyPassword(string $plainPassword, string $hash): bool
    {
        return password_verify($plainPassword, $hash);
    }

    /**
     * Hash a plain-text password using bcrypt (PASSWORD_DEFAULT).
     * Use this whenever storing or updating a password.
     *
     * @param  string $plainPassword
     * @return string  The bcrypt hash.
     */
    public static function hashPassword(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    /**
     * Fetch all users with their role name, ordered by creation date.
     */
    public function allWithRoles(string $direction = 'DESC'): array
    {
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT u.id, u.first_name, u.last_name, u.email,
                       u.student_id, u.created_at, u.is_active,
                       r.id AS role_id, r.name AS role_name, r.slug AS role
                FROM `users` u
                LEFT JOIN `roles` r ON r.id = u.role_id
                ORDER BY u.created_at {$direction}";

        return $this->query($sql)->fetchAll();
    }

    /**
     * Fetch paginated users with their role info.
     *
     * @param  int   $page     1-based page number
     * @param  int   $perPage  Number of items per page
     * @param  string $direction
     * @return array{rows: array, total: int, pages: int, page: int}
     */
    public function getPaginatedUsers(int $page = 1, int $perPage = 50, string $direction = 'DESC'): array
    {
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $countSql = "SELECT COUNT(*) FROM `users`";
        $total = (int) $this->query($countSql)->fetchColumn();

        $pages  = $perPage > 0 ? (int) ceil($total / $perPage) : 1;
        $page   = max(1, min($page, $pages ?: 1));
        $offset = ($page - 1) * $perPage;

        $rowSql = "SELECT u.id, u.first_name, u.last_name, u.email,
                          u.student_id, u.created_at, u.is_active,
                          r.id AS role_id, r.name AS role_name, r.slug AS role
                   FROM `users` u
                   LEFT JOIN `roles` r ON r.id = u.role_id
                   ORDER BY u.created_at {$direction}
                   LIMIT :lim OFFSET :off";

        $stmt = $this->db->prepare($rowSql);
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'rows'  => $stmt->fetchAll(),
            'total' => $total,
            'pages' => $pages,
            'page'  => $page,
        ];
    }

    /**
     * Find a single user with their role info.
     */
    public function findWithRole(int $id): array|false
    {
        $sql = "SELECT u.*, r.name AS role_name, r.slug AS role
                FROM `users` u
                LEFT JOIN `roles` r ON r.id = u.role_id
                WHERE u.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Check if an email already exists (optionally excluding a user by ID).
     */
    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $sql  = "SELECT COUNT(*) FROM `users` WHERE email = :email AND id != :exclude";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email, ':exclude' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Check if a student_id already exists (optionally excluding a user by ID).
     */
    public function studentIdExists(string $studentId, int $excludeId = 0): bool
    {
        $sql  = "SELECT COUNT(*) FROM `users` WHERE student_id = :sid AND id != :exclude";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sid' => $studentId, ':exclude' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Create a new user, returning the new ID or false on failure.
     */
    public function createUser(array $data): int|false
    {
        $payload = [
            'role_id'    => (int) $data['role_id'],
            'first_name' => trim($data['first_name']),
            'last_name'  => trim($data['last_name']),
            'email'      => strtolower(trim($data['email'])),
            'password'   => self::hashPassword($data['password']),
            'student_id' => !empty($data['student_id']) ? trim($data['student_id']) : null,
            'is_active'  => 1,
        ];

        return $this->insert($payload);
    }

    /**
     * Update an existing user.
     * Password is only updated when $data['password'] is non-empty.
     */
    public function updateUser(int $id, array $data): bool
    {
        $payload = [
            'role_id'    => (int) $data['role_id'],
            'first_name' => trim($data['first_name']),
            'last_name'  => trim($data['last_name']),
            'email'      => strtolower(trim($data['email'])),
            'student_id' => !empty($data['student_id']) ? trim($data['student_id']) : null,
            'is_active'  => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ];

        if (!empty($data['password'])) {
            $payload['password'] = self::hashPassword($data['password']);
        }

        return $this->update($id, $payload);
    }

    /**
     * Toggle active status for a user.
     */
    public function setActive(int $id, bool $active): bool
    {
        return $this->update($id, ['is_active' => $active ? 1 : 0]);
    }

    /**
     * Reset a user's password.
     */
    public function resetPassword(int $id, string $newPassword): bool
    {
        return $this->update($id, ['password' => self::hashPassword($newPassword)]);
    }

    /**
     * Count users grouped by role (for dashboard stats).
     */
    public function countByRole(): array
    {
        $sql = "SELECT r.slug, r.name, COUNT(u.id) AS total
                FROM `roles` r
                LEFT JOIN `users` u ON u.role_id = r.id
                GROUP BY r.id";
        return $this->query($sql)->fetchAll();
    }

    /**
     * Fetch all students (role = student) for violation form selects.
     */
    public function allStudents(): array
    {
        $sql = "SELECT u.id, u.first_name, u.last_name, u.student_id
                FROM `users` u
                JOIN `roles` r ON r.id = u.role_id
                WHERE r.slug = 'student' AND u.is_active = 1
                ORDER BY u.last_name, u.first_name";
        return $this->query($sql)->fetchAll();
    }
}
