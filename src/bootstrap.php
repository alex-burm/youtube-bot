<?php

use DI\Container;

$container = new Container();

$container->set(\App\Service\GoogleClient::class, function () {
    return new \App\Service\GoogleClient(
        __DIR__ . '/../config/google-credentials.json',
        __DIR__ . '/../storage/tokens/google-token.json',
        'your-email@example.com'
    );
});

return $container;
