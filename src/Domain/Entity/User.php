<?php

declare(strict_types=1);

namespace App\Domain\Entity;

class User
{
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly string $token,
        public readonly string $createdAt
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['username'],
            $data['token'],
            $data['created_at']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'token' => $this->token,
            'created_at' => $this->createdAt
        ];
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'created_at' => $this->createdAt
        ];
    }
}
