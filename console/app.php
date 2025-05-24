#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use App\Command;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$application = new Application();

$application->add(new Command\FetchCaptionsCommand(
    googleClient: $container->get(\App\Service\GoogleClient::class),
    videoRepository: $container->get(\App\Repository\VideoRepository::class),
));

$application->add(new Command\FetchListCommand(
    googleClient: $container->get(\App\Service\GoogleClient::class),
    videoRepository: $container->get(\App\Repository\VideoRepository::class),
));

$application->add(new Command\IndexCaptionsCommand(
    gptClient: $container->get(\App\Service\GptClient::class),
    cohereClient: $container->get(\App\Service\CohereClient::class),
    pineconeClient: $container->get(\App\Service\PineconeClient::class),
    videoRepository: $container->get(\App\Repository\VideoRepository::class),
));

$application->add(new Command\SearchCaptionsCommand(
    gptClient: $container->get(\App\Service\GptClient::class),
    pineconeClient: $container->get(\App\Service\PineconeClient::class),
    videoRepository: $container->get(\App\Repository\VideoRepository::class),
));

$application->add(new Command\VideoListCommand(
    videoRepository: $container->get(\App\Repository\VideoRepository::class),
));

$application->run();
