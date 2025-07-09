<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\User;
use App\Infrastructure\Database\DatabaseConnection;
use PDO;

class UserRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = DatabaseConnection::getInstance()->getConnection();
    }

    public function create(User $user): bool
    {
        $sql = "INSERT INTO users (id, username, token, created_at) VALUES (?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            $user->id,
            $user->username,
            $user->token,
            $user->createdAt
        ]);
    }

    public function findById(string $id): ?User
    {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$id]);

        $result = $stmt->fetch();
        return $result ? User::fromArray($result) : null;
    }

    public function findByUsername(string $username): ?User
    {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$username]);

        $result = $stmt->fetch();
        return $result ? User::fromArray($result) : null;
    }

    public function findByToken(string $token): ?User
    {
        $sql = "SELECT * FROM users WHERE token = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$token]);

        $result = $stmt->fetch();
        return $result ? User::fromArray($result) : null;
    }

    public function existsByUsername(string $username): bool
    {
        $sql = "SELECT COUNT(*) FROM users WHERE username = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$username]);

        return $stmt->fetchColumn() > 0;
    }
}
