#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use App\Command\FetchCaptionsCommand;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$application = new Application();
$application->add(new FetchCaptionsCommand($container->get(\App\Service\GoogleClient::class)));
$application->run();
