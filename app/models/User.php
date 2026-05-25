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
                       r.name AS role_name, r.slug AS role
                FROM `users` u
                LEFT JOIN `roles` r ON r.id = u.role_id
                ORDER BY u.created_at {$direction}";

        return $this->query($sql)->fetchAll();
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
}
