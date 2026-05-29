<?php

/**
 * Notification Model
 *
 * Handles all database operations for the notifications table.
 *
 * Methods:
 *   createNotification($data)         — insert a notification record
 *   getUserNotifications($userId)     — fetch all notifications for a user (newest first)
 *   getRecentNotifications($userId, $limit) — latest N notifications
 *   markAsRead($id, $userId)          — mark one notification read (ownership enforced)
 *   markAllAsRead($userId)            — mark all unread for a user as read
 *   getUnreadCount($userId)           — count unread notifications
 */

class Notification extends Model
{
    protected string $table = 'notifications';

    // =========================================================================
    // CREATE
    // =========================================================================

    /**
     * Insert a new notification.
     *
     * Required keys in $data:
     *   - user_id  (int)
     *   - title    (string)
     *   - message  (string)
     *
     * Optional keys:
     *   - type            (info|success|warning|danger) default 'info'
     *   - reference_id    (int|null)
     *   - reference_table (string|null)
     *
     * @param  array $data
     * @return int|false  Inserted notification ID on success, false on failure
     */
    public function createNotification(array $data): int|false
    {
        try {
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare(
                'INSERT INTO notifications
                    (user_id, title, message, type, reference_id, reference_table)
                 VALUES
                    (:user_id, :title, :message, :type, :reference_id, :reference_table)'
            );

            $stmt->execute([
                ':user_id'         => (int) $data['user_id'],
                ':title'           => $data['title'],
                ':message'         => $data['message'],
                ':type'            => $data['type']            ?? 'info',
                ':reference_id'    => $data['reference_id']    ?? null,
                ':reference_table' => $data['reference_table'] ?? null,
            ]);

            return (int) $db->lastInsertId();

        } catch (PDOException $e) {
            error_log('ACVMS Notification::createNotification — ' . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // READ
    // =========================================================================

    /**
     * Fetch all notifications for a user, newest first.
     *
     * @param  int   $userId
     * @return array
     */
    public function getUserNotifications(int $userId): array
    {
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare(
                'SELECT * FROM notifications
                  WHERE user_id = :uid
                  ORDER BY created_at DESC'
            );
            $stmt->execute([':uid' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('ACVMS Notification::getUserNotifications — ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch the N most recent notifications for a user.
     *
     * @param  int $userId
     * @param  int $limit  (default 10)
     * @return array
     */
    public function getRecentNotifications(int $userId, int $limit = 10): array
    {
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare(
                'SELECT * FROM notifications
                  WHERE user_id = :uid
                  ORDER BY created_at DESC
                  LIMIT :lim'
            );
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit,  PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('ACVMS Notification::getRecentNotifications — ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count unread notifications for a user.
     *
     * @param  int $userId
     * @return int
     */
    public function getUnreadCount(int $userId): int
    {
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare(
                'SELECT COUNT(*) FROM notifications
                  WHERE user_id = :uid AND is_read = 0'
            );
            $stmt->execute([':uid' => $userId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('ACVMS Notification::getUnreadCount — ' . $e->getMessage());
            return 0;
        }
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    /**
     * Mark a single notification as read.
     * Enforces ownership: only the owning user may mark it read.
     *
     * @param  int $id
     * @param  int $userId
     * @return bool
     */
    public function markAsRead(int $id, int $userId): bool
    {
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare(
                'UPDATE notifications
                    SET is_read = 1
                  WHERE id = :id AND user_id = :uid'
            );
            $stmt->execute([':id' => $id, ':uid' => $userId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('ACVMS Notification::markAsRead — ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark all notifications for a user as read.
     *
     * @param  int $userId
     * @return bool
     */
    public function markAllAsRead(int $userId): bool
    {
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare(
                'UPDATE notifications
                    SET is_read = 1
                  WHERE user_id = :uid AND is_read = 0'
            );
            $stmt->execute([':uid' => $userId]);
            return true;
        } catch (PDOException $e) {
            error_log('ACVMS Notification::markAllAsRead — ' . $e->getMessage());
            return false;
        }
    }
}
