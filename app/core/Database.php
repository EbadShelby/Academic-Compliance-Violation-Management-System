<?php

/**
 * Database — PDO Singleton
 *
 * Usage:
 *   $db = Database::getInstance();
 *   $pdo = $db->getConnection();
 */

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $config = require BASE_PATH . '/app/config/database.php';

        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (PDOException $e) {
            // Never leak credentials in production
            if (APP_ENV === 'development') {
                die('Database connection failed: ' . $e->getMessage());
            }
            die('Database connection failed. Please contact the administrator.');
        }
    }

    /**
     * Returns the single Database instance.
     */
    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Returns the underlying PDO connection.
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    // Prevent cloning / unserialization of singleton
    private function __clone() {}
    public function __wakeup(): never
    {
        throw new \Exception('Cannot unserialize a singleton.');
    }
}
