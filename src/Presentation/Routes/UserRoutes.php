<?php

declare(strict_types=1);

namespace App\Presentation\Routes;

use App\Application\Service\UserService;
use App\Infrastructure\Repository\UserRepository;
use App\Infrastructure\Validation\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteCollectorProxy;

class UserRoutes
{
    private UserService $userService;

    public function __construct(private RouteCollectorProxy $group)
    {
        $this->userService = new UserService(new UserRepository());
    }

    public function register(): void
    {
        $this->group->post('/users', [$this, 'createUser']);
        $this->group->get('/users/{id}', [$this, 'getUser']);
        $this->group->post('/users/authenticate', [$this, 'authenticateUser']);
    }

        public function createUser(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = Validator::sanitizeInput($request->getParsedBody() ?? []);
            
            if (!isset($data['username'])) {
                $error = ['error' => 'Username is required'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(400);
            }

            $validationErrors = Validator::validateUsername($data['username']);
            if (!empty($validationErrors)) {
                $error = ['error' => implode(', ', $validationErrors)];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(400);
            }

            $user = $this->userService->createUser($data['username']);
            $response->getBody()->write(json_encode($user->toArray()));
            return $response->withStatus(201);
        } catch (\Exception $e) {
            $error = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(400);
        }
    }

    public function getUser(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $userId = $args['id'];
            $user = $this->userService->getUserById($userId);

            if (!$user) {
                $error = ['error' => 'User not found'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(404);
            }

            $response->getBody()->write(json_encode($user->toPublicArray()));
            return $response->withStatus(200);
        } catch (\Exception $e) {
            $error = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(500);
        }
    }

        public function authenticateUser(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = Validator::sanitizeInput($request->getParsedBody() ?? []);
            
            if (!isset($data['token'])) {
                $error = ['error' => 'Token is required'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(400);
            }



            $user = $this->userService->getUserByToken($data['token']);
            
            if (!$user) {
                $error = ['error' => 'Invalid token'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(401);
            }

            $response->getBody()->write(json_encode($user->toPublicArray()));
            return $response->withStatus(200);
        } catch (\Exception $e) {
            $error = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(500);
        }
    }
}
