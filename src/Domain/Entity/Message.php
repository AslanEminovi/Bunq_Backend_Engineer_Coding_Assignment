<?php

declare(strict_types=1);

namespace App\Domain\Entity;

class Message
{
    public function __construct(
        public readonly string $id,
        public readonly string $groupId,
        public readonly string $userId,
        public readonly string $content,
        public readonly string $createdAt
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['group_id'],
            $data['user_id'],
            $data['content'],
            $data['created_at']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'group_id' => $this->groupId,
            'user_id' => $this->userId,
            'content' => $this->content,
            'created_at' => $this->createdAt
        ];
    }
}
