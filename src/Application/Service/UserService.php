<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\User;
use App\Infrastructure\Repository\UserRepository;
use Ramsey\Uuid\Uuid;

class UserService
{
    public function __construct(private UserRepository $userRepository) {}

    public function createUser(string $username): User
    {
        if ($this->userRepository->existsByUsername($username)) {
            throw new \Exception('Username already exists');
        }

        $user = new User(
            Uuid::uuid4()->toString(),
            $username,
            $this->generateToken(),
            date('Y-m-d H:i:s')
        );

        if (!$this->userRepository->create($user)) {
            throw new \Exception('Failed to create user');
        }

        return $user;
    }

    public function getUserById(string $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function getUserByUsername(string $username): ?User
    {
        return $this->userRepository->findByUsername($username);
    }

    public function getUserByToken(string $token): ?User
    {
        return $this->userRepository->findByToken($token);
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
