<?php

declare(strict_types=1);

namespace App\Presentation\Routes;

use App\Application\Service\GroupService;
use App\Infrastructure\Repository\GroupRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteCollectorProxy;

class GroupRoutes
{
    private GroupService $groupService;

    public function __construct(private RouteCollectorProxy $group)
    {
        $this->groupService = new GroupService(new GroupRepository());
    }

    public function register(): void
    {
        $this->group->post('/groups', [$this, 'createGroup']);
        $this->group->get('/groups', [$this, 'listGroups']);
        $this->group->get('/groups/{id}', [$this, 'getGroup']);
        $this->group->post('/groups/{id}/join', [$this, 'joinGroup']);
        $this->group->get('/groups/{id}/members', [$this, 'getGroupMembers']);
    }

    public function createGroup(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            $headers = $request->getHeaders();

            $userToken = $headers['Authorization'][0] ?? $headers['authorization'][0] ?? null;
            if (!$userToken) {
                $error = ['error' => 'Authorization header required'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(401);
            }

            if (!isset($data['name']) || empty(trim($data['name']))) {
                $error = ['error' => 'Group name is required'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(400);
            }

            $group = $this->groupService->createGroup(
                trim($data['name']),
                $data['description'] ?? null,
                str_replace('Bearer ', '', $userToken)
            );

            $response->getBody()->write(json_encode($group->toArray()));
            return $response->withStatus(201);
        } catch (\Exception $e) {
            $error = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(400);
        }
    }

    public function listGroups(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $groups = $this->groupService->getAllGroups();
            $groupsArray = array_map(fn($group) => $group->toArray(), $groups);

            $response->getBody()->write(json_encode(['groups' => $groupsArray]));
            return $response->withStatus(200);
        } catch (\Exception $e) {
            $error = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(500);
        }
    }

    public function getGroup(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $groupId = $args['id'];
            $group = $this->groupService->getGroupById($groupId);

            if (!$group) {
                $error = ['error' => 'Group not found'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(404);
            }

            $response->getBody()->write(json_encode($group->toArray()));
            return $response->withStatus(200);
        } catch (\Exception $e) {
            $error = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(500);
        }
    }

    public function joinGroup(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $groupId = $args['id'];
            $headers = $request->getHeaders();

            $userToken = $headers['Authorization'][0] ?? $headers['authorization'][0] ?? null;
            if (!$userToken) {
                $error = ['error' => 'Authorization header required'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(401);
            }

            $success = $this->groupService->joinGroup($groupId, str_replace('Bearer ', '', $userToken));

            if (!$success) {
                $error = ['error' => 'Failed to join group'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(400);
            }

            $response->getBody()->write(json_encode(['message' => 'Successfully joined group']));
            return $response->withStatus(200);
        } catch (\Exception $e) {
            $error = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(400);
        }
    }

    public function getGroupMembers(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $groupId = $args['id'];
            $members = $this->groupService->getGroupMembers($groupId);

            $response->getBody()->write(json_encode(['members' => $members]));
            return $response->withStatus(200);
        } catch (\Exception $e) {
            $error = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(500);
        }
    }
}
