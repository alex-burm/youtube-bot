<?php

use Slim\Factory\AppFactory;
use Slim\Handlers\Strategies\RequestResponseNamedArgs;

require __DIR__ . '/../vendor/autoload.php';

$container = require_once __DIR__ . '/../src/bootstrap.php';

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->getRouteCollector()->setDefaultInvocationStrategy(new RequestResponseNamedArgs());
(require __DIR__ . '/../src/routes.php')($app);

$app->run();
