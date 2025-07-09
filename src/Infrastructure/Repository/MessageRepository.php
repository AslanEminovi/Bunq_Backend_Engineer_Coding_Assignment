<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Message;
use App\Infrastructure\Database\DatabaseConnection;
use PDO;

class MessageRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = DatabaseConnection::getInstance()->getConnection();
    }

    public function create(Message $message): bool
    {
        $sql = "INSERT INTO messages (id, group_id, user_id, content, created_at) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            $message->id,
            $message->groupId,
            $message->userId,
            $message->content,
            $message->createdAt
        ]);
    }

    public function findByGroupId(string $groupId, int $limit = 50, int $offset = 0): array
    {
        $sql = "
            SELECT m.*, u.username 
            FROM messages m
            JOIN users u ON m.user_id = u.id
            WHERE m.group_id = ? 
            ORDER BY m.created_at DESC 
            LIMIT ? OFFSET ?
        ";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$groupId, $limit, $offset]);

        $messages = [];
        while ($row = $stmt->fetch()) {
            $message = Message::fromArray($row);
            $messages[] = $message;
        }

        return array_reverse($messages); // Return in chronological order
    }

    public function findById(string $id): ?Message
    {
        $sql = "SELECT * FROM messages WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$id]);

        $result = $stmt->fetch();
        return $result ? Message::fromArray($result) : null;
    }

    public function countByGroupId(string $groupId): int
    {
        $sql = "SELECT COUNT(*) FROM messages WHERE group_id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$groupId]);

        return (int) $stmt->fetchColumn();
    }
}
