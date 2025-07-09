<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\Message;
use App\Infrastructure\Repository\MessageRepository;
use App\Infrastructure\Repository\UserRepository;
use App\Infrastructure\Repository\GroupRepository;
use Ramsey\Uuid\Uuid;

class MessageService
{
    private UserRepository $userRepository;
    private GroupRepository $groupRepository;

    public function __construct(
        private MessageRepository $messageRepository
    ) {
        $this->userRepository = new UserRepository();
        $this->groupRepository = new GroupRepository();
    }

    public function sendMessage(string $groupId, string $content, string $userToken): Message
    {
        $user = $this->userRepository->findByToken($userToken);
        if (!$user) {
            throw new \Exception('Invalid user token');
        }

        $group = $this->groupRepository->findById($groupId);
        if (!$group) {
            throw new \Exception('Group not found');
        }

        // Check if user is a member of the group
        if (!$this->groupRepository->isMember($groupId, $user->id)) {
            throw new \Exception('User is not a member of this group');
        }

        $message = new Message(
            Uuid::uuid4()->toString(),
            $groupId,
            $user->id,
            $content,
            date('Y-m-d H:i:s')
        );

        if (!$this->messageRepository->create($message)) {
            throw new \Exception('Failed to send message');
        }

        return $message;
    }

    public function getGroupMessages(string $groupId, int $limit = 50, int $offset = 0): array
    {
        $group = $this->groupRepository->findById($groupId);
        if (!$group) {
            throw new \Exception('Group not found');
        }

        return $this->messageRepository->findByGroupId($groupId, $limit, $offset);
    }

    public function getMessageById(string $id): ?Message
    {
        return $this->messageRepository->findById($id);
    }
}
