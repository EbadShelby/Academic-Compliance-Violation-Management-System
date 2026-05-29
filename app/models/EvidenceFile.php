<?php

/**
 * EvidenceFile Model
 *
 * Handles saving uploaded file records to the evidence_files table.
 *
 * Schema columns used:
 *   id, violation_id, uploaded_by, file_name, file_path,
 *   mime_type, file_size, created_at
 */

class EvidenceFile extends Model
{
    protected string $table = 'evidence_files';

    /**
     * Persist a single evidence file record.
     *
     * Expected $data keys:
     *   violation_id  (int)
     *   uploaded_by   (int)  — user_id of uploader
     *   file_name     (string) — original filename (sanitised)
     *   file_path     (string) — path relative to storage root
     *   mime_type     (string|null)
     *   file_size     (int|null) — bytes
     *
     * @param  array $data
     * @return int   New record ID
     */
    public function saveEvidence(array $data): int
    {
        $payload = [
            'violation_id' => (int) $data['violation_id'],
            'uploaded_by'  => (int) $data['uploaded_by'],
            'file_name'    => $data['file_name'],
            'file_path'    => $data['file_path'],
            'mime_type'    => $data['mime_type']  ?? null,
            'file_size'    => isset($data['file_size']) ? (int) $data['file_size'] : null,
        ];

        return $this->insert($payload);
    }

    /**
     * Fetch all evidence files for a violation.
     */
    public function findByViolation(int $violationId): array
    {
        $sql = "SELECT ef.*, CONCAT(u.first_name, ' ', u.last_name) AS uploader_name
                FROM `evidence_files` ef
                JOIN `users` u ON u.id = ef.uploaded_by
                WHERE ef.violation_id = :vid
                ORDER BY ef.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':vid' => $violationId]);
        return $stmt->fetchAll();
    }

    /**
     * Alias for findByViolation() — satisfies Phase 9 spec method name.
     */
    public function getByViolation(int $violationId): array
    {
        return $this->findByViolation($violationId);
    }
}
