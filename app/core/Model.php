<?php

/**
 * Base Model
 *
 * All models extend this class.
 * Provides a shared PDO connection and common CRUD helpers.
 */

abstract class Model
{
    protected PDO $db;

    /** Override in child models to set the table name. */
    protected string $table = '';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Generic CRUD helpers ─────────────────────────────────────────────────

    /**
     * Fetch all rows from the model's table.
     */
    public function all(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $stmt = $this->db->query("SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` {$direction}");
        return $stmt->fetchAll();
    }

    /**
     * Find a single row by primary key.
     */
    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Find rows matching a single column/value.
     */
    public function findBy(string $column, mixed $value): array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$column}` = :value");
        $stmt->execute([':value' => $value]);
        return $stmt->fetchAll();
    }

    /**
     * Find one row matching a single column/value.
     */
    public function findOneBy(string $column, mixed $value): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$column}` = :value LIMIT 1");
        $stmt->execute([':value' => $value]);
        return $stmt->fetch();
    }

    /**
     * Insert a row and return the new ID.
     */
    public function insert(array $data): int
    {
        $columns      = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($data)));
        $placeholders = implode(', ', array_map(fn($c) => ":{$c}", array_keys($data)));

        $stmt = $this->db->prepare("INSERT INTO `{$this->table}` ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a row by ID.
     */
    public function update(int $id, array $data): bool
    {
        $set = implode(', ', array_map(fn($c) => "`{$c}` = :{$c}", array_keys($data)));

        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET {$set} WHERE id = :id");
        $data[':id'] = $id;
        return $stmt->execute($data);
    }

    /**
     * Delete a row by ID.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Count all rows.
     */
    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM `{$this->table}`")->fetchColumn();
    }

    /**
     * Execute a raw query (returns statement).
     */
    protected function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
