<?php

/**
 * ViolationAction Model
 *
 * Records lifecycle actions taken on a violation case.
 * Every status change, rejection, sanction, and close action
 * writes a row here for the case history timeline.
 *
 * Schema: violation_actions
 *   id, violation_id, actor_id, action_type, note, created_at
 */

class ViolationAction extends Model
{
    protected string $table = 'violation_actions';

    /**
     * Insert a new violation action record.
     *
     * Expected $data keys:
     *   violation_id  (int)         — FK to violations.id
     *   actor_id      (int)         — FK to users.id (the admin/teacher acting)
     *   action_type   (string)      — e.g. 'status_changed', 'case_rejected',
     *                                       'sanction_assigned', 'case_closed'
     *   note          (string|null) — human-readable description of the action
     *
     * @param  array $data
     * @return int   New action ID
     */
    public function createAction(array $data): int
    {
        $payload = [
            'violation_id' => (int) $data['violation_id'],
            'actor_id'     => (int) $data['actor_id'],
            'action_type'  => trim($data['action_type']),
            'note'         => isset($data['note']) ? trim($data['note']) : null,
        ];

        return $this->insert($payload);
    }

    /**
     * Fetch all actions for a given violation, newest first.
     *
     * Joins users so the view can display the actor's full name.
     *
     * @param  int   $violationId
     * @return array
     */
    public function findByViolation(int $violationId): array
    {
        $sql = "SELECT
                    va.*,
                    CONCAT(u.first_name, ' ', u.last_name) AS actor_name,
                    u.email                                  AS actor_email
                FROM `violation_actions` va
                JOIN `users` u ON u.id = va.actor_id
                WHERE va.violation_id = :vid
                ORDER BY va.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':vid' => $violationId]);
        return $stmt->fetchAll();
    }
}
