<?php

declare(strict_types=1);

namespace App\Presentation\Routes;

use App\Application\Service\MessageService;
use App\Infrastructure\Repository\MessageRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteCollectorProxy;

class MessageRoutes
{
    private MessageService $messageService;

    public function __construct(private RouteCollectorProxy $group)
    {
        $this->messageService = new MessageService(new MessageRepository());
    }

    public function register(): void
    {
        $this->group->post('/groups/{groupId}/messages', [$this, 'sendMessage']);
        $this->group->get('/groups/{groupId}/messages', [$this, 'getMessages']);
    }

    public function sendMessage(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $groupId = $args['groupId'];
            $data = $request->getParsedBody();
            $headers = $request->getHeaders();

            $userToken = $headers['Authorization'][0] ?? $headers['authorization'][0] ?? null;
            if (!$userToken) {
                $error = ['error' => 'Authorization header required'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(401);
            }

            if (!isset($data['content']) || empty(trim($data['content']))) {
                $error = ['error' => 'Message content is required'];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(400);
            }

            $message = $this->messageService->sendMessage(
                $groupId,
                trim($data['content']),
                str_replace('Bearer ', '', $userToken)
            );

            $response->getBody()->write(json_encode($message->toArray()));
            return $response->withStatus(201);
        } catch (\Exception $e) {
            $error = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(400);
        }
    }

    public function getMessages(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $groupId = $args['groupId'];
            $queryParams = $request->getQueryParams();

            $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 50;
            $offset = isset($queryParams['offset']) ? (int)$queryParams['offset'] : 0;

            $messages = $this->messageService->getGroupMessages($groupId, $limit, $offset);
            $messagesArray = array_map(fn($message) => $message->toArray(), $messages);

            $response->getBody()->write(json_encode(['messages' => $messagesArray]));
            return $response->withStatus(200);
        } catch (\Exception $e) {
            $error = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(500);
        }
    }
}
