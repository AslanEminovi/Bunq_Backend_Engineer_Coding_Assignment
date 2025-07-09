<?php

declare(strict_types=1);

namespace App\Domain\Entity;

class Group
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $createdBy,
        public readonly string $createdAt
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['description'] ?? null,
            $data['created_by'],
            $data['created_at']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt
        ];
    }
}
