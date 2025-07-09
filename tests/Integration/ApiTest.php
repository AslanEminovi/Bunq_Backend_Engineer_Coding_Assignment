<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Application\App;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\StreamFactory;

class ApiTest extends TestCase
{
    private \Slim\App $app;

    protected function setUp(): void
    {
        $this->app = AppFactory::create();
        (new App($this->app))->configure();

        // Clean up test database
        $dbPath = __DIR__ . '/../../database/chat.sqlite';
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test database
        $dbPath = __DIR__ . '/../../database/chat.sqlite';
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
    }

    public function testHealthEndpoint(): void
    {
        $request = (new RequestFactory())->createRequest('GET', '/health');
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertEquals(['status' => 'healthy'], $body);
    }

    public function testCreateUser(): void
    {
        $request = (new RequestFactory())->createRequest('POST', '/api/v1/users')
            ->withHeader('Content-Type', 'application/json');

        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream(json_encode(['username' => 'testuser']));
        $request = $request->withBody($body);

        $response = $this->app->handle($request);

        $this->assertEquals(201, $response->getStatusCode());
        $responseData = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('username', $responseData);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertEquals('testuser', $responseData['username']);
    }

    public function testCreateUserWithInvalidUsername(): void
    {
        $request = (new RequestFactory())->createRequest('POST', '/api/v1/users')
            ->withHeader('Content-Type', 'application/json');

        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream(json_encode(['username' => 'ab'])); // Too short
        $request = $request->withBody($body);

        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseData);
    }

    public function testCreateGroupAndJoin(): void
    {
        // First create a user
        $userResponse = $this->createTestUser('testuser');
        $userData = json_decode((string) $userResponse->getBody(), true);
        $token = $userData['token'];

        // Create a group
        $request = (new RequestFactory())->createRequest('POST', '/api/v1/groups')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Authorization', 'Bearer ' . $token);

        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream(json_encode([
            'name' => 'Test Group',
            'description' => 'A test group'
        ]));
        $request = $request->withBody($body);

        $response = $this->app->handle($request);

        $this->assertEquals(201, $response->getStatusCode());
        $groupData = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('id', $groupData);
        $this->assertEquals('Test Group', $groupData['name']);
        $this->assertEquals('A test group', $groupData['description']);

        // Test listing groups
        $listRequest = (new RequestFactory())->createRequest('GET', '/api/v1/groups');
        $listResponse = $this->app->handle($listRequest);

        $this->assertEquals(200, $listResponse->getStatusCode());
        $listData = json_decode((string) $listResponse->getBody(), true);
        $this->assertArrayHasKey('groups', $listData);
        $this->assertCount(1, $listData['groups']);
    }

    public function testSendAndReceiveMessages(): void
    {
        // Create user and group
        $userResponse = $this->createTestUser('testuser');
        $userData = json_decode((string) $userResponse->getBody(), true);
        $token = $userData['token'];

        $groupResponse = $this->createTestGroup('Test Group', 'Test description', $token);
        $groupData = json_decode((string) $groupResponse->getBody(), true);
        $groupId = $groupData['id'];

        // Send a message
        $request = (new RequestFactory())->createRequest('POST', "/api/v1/groups/{$groupId}/messages")
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Authorization', 'Bearer ' . $token);

        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream(json_encode(['content' => 'Hello, world!']));
        $request = $request->withBody($body);

        $response = $this->app->handle($request);

        $this->assertEquals(201, $response->getStatusCode());
        $messageData = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('id', $messageData);
        $this->assertEquals('Hello, world!', $messageData['content']);

        // Get messages
        $getRequest = (new RequestFactory())->createRequest('GET', "/api/v1/groups/{$groupId}/messages");
        $getResponse = $this->app->handle($getRequest);

        $this->assertEquals(200, $getResponse->getStatusCode());
        $messagesData = json_decode((string) $getResponse->getBody(), true);
        $this->assertArrayHasKey('messages', $messagesData);
        $this->assertCount(1, $messagesData['messages']);
        $this->assertEquals('Hello, world!', $messagesData['messages'][0]['content']);
    }

    private function createTestUser(string $username): ResponseInterface
    {
        $request = (new RequestFactory())->createRequest('POST', '/api/v1/users')
            ->withHeader('Content-Type', 'application/json');

        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream(json_encode(['username' => $username]));
        $request = $request->withBody($body);

        return $this->app->handle($request);
    }

    private function createTestGroup(string $name, string $description, string $token): ResponseInterface
    {
        $request = (new RequestFactory())->createRequest('POST', '/api/v1/groups')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Authorization', 'Bearer ' . $token);

        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream(json_encode([
            'name' => $name,
            'description' => $description
        ]));
        $request = $request->withBody($body);

        return $this->app->handle($request);
    }
}
