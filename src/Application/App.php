<?php

declare(strict_types=1);

namespace App\Application;

use App\Infrastructure\Database\DatabaseConnection;
use App\Infrastructure\Middleware\CorsMiddleware;
use App\Infrastructure\Middleware\JsonMiddleware;
use App\Presentation\Routes\GroupRoutes;
use App\Presentation\Routes\MessageRoutes;
use App\Presentation\Routes\UserRoutes;
use Slim\App as SlimApp;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteCollectorProxy;

class App
{
    private SlimApp $app;

    public function __construct(SlimApp $app)
    {
        $this->app = $app;
    }

    public function configure(): void
    {
        $this->configureMiddleware();
        $this->configureRoutes();
        $this->configureErrorHandling();
        $this->initializeDatabase();
    }

    private function configureMiddleware(): void
    {
        $this->app->add(new JsonMiddleware());
        $this->app->add(new CorsMiddleware());
        $this->app->addBodyParsingMiddleware();
        $this->app->addRoutingMiddleware();
        $this->app->addErrorMiddleware(true, true, true);
    }

    private function configureRoutes(): void
    {
        $this->app->group('/api/v1', function (RouteCollectorProxy $group) {
            (new UserRoutes($group))->register();
            (new GroupRoutes($group))->register();
            (new MessageRoutes($group))->register();
        });

        // Health check endpoint
        $this->app->get('/health', function ($request, $response) {
            $response->getBody()->write(json_encode(['status' => 'healthy']));
            return $response->withHeader('Content-Type', 'application/json');
        });
    }

    private function configureErrorHandling(): void
    {
        $this->app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
            throw new HttpNotFoundException($request);
        });
    }

    private function initializeDatabase(): void
    {
        DatabaseConnection::getInstance()->initializeTables();
    }
}
