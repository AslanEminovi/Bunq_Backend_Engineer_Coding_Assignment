<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Group;
use App\Infrastructure\Database\DatabaseConnection;
use PDO;

class GroupRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = DatabaseConnection::getInstance()->getConnection();
    }

    public function create(Group $group): bool
    {
        $sql = "INSERT INTO groups (id, name, description, created_by, created_at) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            $group->id,
            $group->name,
            $group->description,
            $group->createdBy,
            $group->createdAt
        ]);
    }

    public function findById(string $id): ?Group
    {
        $sql = "SELECT * FROM groups WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$id]);

        $result = $stmt->fetch();
        return $result ? Group::fromArray($result) : null;
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM groups ORDER BY created_at DESC";
        $stmt = $this->connection->query($sql);

        $groups = [];
        while ($row = $stmt->fetch()) {
            $groups[] = Group::fromArray($row);
        }

        return $groups;
    }

    public function addMember(string $groupId, string $userId): bool
    {
        $sql = "INSERT INTO group_members (id, group_id, user_id, joined_at) VALUES (?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            \Ramsey\Uuid\Uuid::uuid4()->toString(),
            $groupId,
            $userId,
            date('Y-m-d H:i:s')
        ]);
    }

    public function isMember(string $groupId, string $userId): bool
    {
        $sql = "SELECT COUNT(*) FROM group_members WHERE group_id = ? AND user_id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$groupId, $userId]);

        return $stmt->fetchColumn() > 0;
    }

    public function getMembers(string $groupId): array
    {
        $sql = "
            SELECT u.id, u.username, u.created_at, gm.joined_at 
            FROM users u 
            JOIN group_members gm ON u.id = gm.user_id 
            WHERE gm.group_id = ? 
            ORDER BY gm.joined_at ASC
        ";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$groupId]);

        return $stmt->fetchAll();
    }
}
