<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\Group;
use App\Infrastructure\Repository\GroupRepository;
use App\Infrastructure\Repository\UserRepository;
use Ramsey\Uuid\Uuid;

class GroupService
{
    private UserRepository $userRepository;

    public function __construct(
        private GroupRepository $groupRepository
    ) {
        $this->userRepository = new UserRepository();
    }

    public function createGroup(string $name, ?string $description, string $userToken): Group
    {
        $user = $this->userRepository->findByToken($userToken);
        if (!$user) {
            throw new \Exception('Invalid user token');
        }

        $group = new Group(
            Uuid::uuid4()->toString(),
            $name,
            $description,
            $user->id,
            date('Y-m-d H:i:s')
        );

        if (!$this->groupRepository->create($group)) {
            throw new \Exception('Failed to create group');
        }

        // Automatically add creator as a member
        $this->groupRepository->addMember($group->id, $user->id);

        return $group;
    }

    public function getAllGroups(): array
    {
        return $this->groupRepository->findAll();
    }

    public function getGroupById(string $id): ?Group
    {
        return $this->groupRepository->findById($id);
    }

    public function joinGroup(string $groupId, string $userToken): bool
    {
        $user = $this->userRepository->findByToken($userToken);
        if (!$user) {
            throw new \Exception('Invalid user token');
        }

        $group = $this->groupRepository->findById($groupId);
        if (!$group) {
            throw new \Exception('Group not found');
        }

        if ($this->groupRepository->isMember($groupId, $user->id)) {
            throw new \Exception('User is already a member of this group');
        }

        return $this->groupRepository->addMember($groupId, $user->id);
    }

    public function getGroupMembers(string $groupId): array
    {
        $group = $this->groupRepository->findById($groupId);
        if (!$group) {
            throw new \Exception('Group not found');
        }

        return $this->groupRepository->getMembers($groupId);
    }
}
