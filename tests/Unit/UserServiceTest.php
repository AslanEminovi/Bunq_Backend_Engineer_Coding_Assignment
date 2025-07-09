<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application\Service\UserService;
use App\Domain\Entity\User;
use App\Infrastructure\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepository|MockObject $userRepositoryMock;

    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->userService = new UserService($this->userRepositoryMock);
    }

    public function testCreateUserSuccessfully(): void
    {
        $username = 'testuser';

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('existsByUsername')
            ->with($username)
            ->willReturn(false);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn(true);

        $user = $this->userService->createUser($username);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($username, $user->username);
        $this->assertNotEmpty($user->id);
        $this->assertNotEmpty($user->token);
    }

    public function testCreateUserThrowsExceptionWhenUsernameExists(): void
    {
        $username = 'existinguser';

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('existsByUsername')
            ->with($username)
            ->willReturn(true);

        $this->userRepositoryMock
            ->expects($this->never())
            ->method('create');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Username already exists');

        $this->userService->createUser($username);
    }

    public function testCreateUserThrowsExceptionWhenRepositoryFails(): void
    {
        $username = 'testuser';

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('existsByUsername')
            ->with($username)
            ->willReturn(false);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to create user');

        $this->userService->createUser($username);
    }

    public function testGetUserById(): void
    {
        $userId = 'test-user-id';
        $expectedUser = new User($userId, 'testuser', 'token123', '2023-01-01 00:00:00');

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($expectedUser);

        $result = $this->userService->getUserById($userId);

        $this->assertEquals($expectedUser, $result);
    }

    public function testGetUserByIdReturnsNullWhenNotFound(): void
    {
        $userId = 'non-existent-id';

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn(null);

        $result = $this->userService->getUserById($userId);

        $this->assertNull($result);
    }

    public function testGetUserByToken(): void
    {
        $token = 'test-token';
        $expectedUser = new User('user-id', 'testuser', $token, '2023-01-01 00:00:00');

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('findByToken')
            ->with($token)
            ->willReturn($expectedUser);

        $result = $this->userService->getUserByToken($token);

        $this->assertEquals($expectedUser, $result);
    }
}
