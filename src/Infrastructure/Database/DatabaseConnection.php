<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use PDO;
use PDOException;

class DatabaseConnection
{
    private static ?DatabaseConnection $instance = null;
    private PDO $connection;
    private string $dbPath;

    private function __construct()
    {
        $this->dbPath = __DIR__ . '/../../../database/chat.sqlite';
        $this->createDatabaseDirectory();
        $this->connect();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    private function createDatabaseDirectory(): void
    {
        $dbDir = dirname($this->dbPath);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
    }

    private function connect(): void
    {
        try {
            $this->connection = new PDO("sqlite:{$this->dbPath}");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->connection->exec('PRAGMA foreign_keys = ON');
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    public function initializeTables(): void
    {
        $this->createUsersTable();
        $this->createGroupsTable();
        $this->createGroupMembersTable();
        $this->createMessagesTable();
    }

    private function createUsersTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id TEXT PRIMARY KEY,
                username VARCHAR(255) UNIQUE NOT NULL,
                token TEXT UNIQUE NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";
        $this->connection->exec($sql);
    }

    private function createGroupsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS groups (
                id TEXT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                created_by TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
            )
        ";
        $this->connection->exec($sql);
    }

    private function createGroupMembersTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS group_members (
                id TEXT PRIMARY KEY,
                group_id TEXT NOT NULL,
                user_id TEXT NOT NULL,
                joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE(group_id, user_id)
            )
        ";
        $this->connection->exec($sql);
    }

    private function createMessagesTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS messages (
                id TEXT PRIMARY KEY,
                group_id TEXT NOT NULL,
                user_id TEXT NOT NULL,
                content TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ";
        $this->connection->exec($sql);
    }
}
