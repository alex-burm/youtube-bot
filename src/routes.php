<?php

use Slim\App;

return function (App $app) {
    $app->get('/', [\App\Controller\HomeController::class, 'index']);
    $app->get('/auth', [\App\Controller\OAuthController::class, 'auth']);
    $app->get('/auth/callback', [\App\Controller\OAuthController::class, 'callback']);
};
