<?php

declare(strict_types=1);

use App\Application\App;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate app
$app = AppFactory::create();

// Configure app
(new App($app))->configure();

// Run app
$app->run();
