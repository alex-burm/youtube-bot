<?php

use DI\Container;

if (false === \file_exists(__DIR__ . '/../config/settings.php')) {
    throw new \RuntimeException('Settings file not found. Please create config/settings.php.');
}

$container = new Container();

$settings = require __DIR__ . '/../config/settings.php';
$container->set('settings', $settings);

$container->set(\PDO::class, function () {
    $pdo = new \PDO('sqlite:' . __DIR__ . '/../storage/database.db');
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    return $pdo;
});

$container->set(\App\Repository\VideoRepository::class, static function ($container) {
    return new \App\Repository\VideoRepository($container->get(\PDO::class));
});

$container->set(\App\Repository\UserRepository::class, static function ($container) {
    return new \App\Repository\UserRepository($container->get(\PDO::class));
});

$container->set(\App\Repository\QueryRepository::class, static function ($container) {
    return new \App\Repository\QueryRepository($container->get(\PDO::class));
});

$container->set(\App\Service\GoogleClient::class, static function () use ($settings) {
    return new \App\Service\GoogleClient($settings['google']);
});

$container->set(\App\Service\CohereClient::class, static function () use ($settings) {
    return new \App\Service\CohereClient($settings['cohere']);
});

$container->set(\App\Service\PineconeClient::class, static function () use ($settings) {
    return new \App\Service\PineconeClient($settings['pinecone']);
});

$container->set(\App\Service\GptClient::class, static function () use ($settings) {
    return new \App\Service\GptClient($settings['gpt']);
});

return $container;
