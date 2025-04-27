<?php

namespace App\Controller;

use App\Service\GoogleClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OAuthController
{
    public function __construct(
        protected GoogleClient $client
    ) {
    }

    public function auth(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $authUrl = $this->client->getClient()->createAuthUrl();
        return $response
            ->withHeader('Location', $authUrl)
            ->withStatus(302);
    }

    public function callback(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['code'])) {
            $response->getBody()->write('No auth code');
            return $response;
        }

        $client = $this->client->getClient();
        $accessToken = $client->fetchAccessTokenWithAuthCode($queryParams['code']);

        if (\array_key_exists('error', $accessToken)) {
            $response->getBody()->write('Error fetching access token: ' . $accessToken['error']);
            return $response;
        }

        $tokenPath = __DIR__ . '/../../storage/tokens/google-token.json';
        \file_put_contents($tokenPath, \json_encode($accessToken));

        $response->getBody()->write('Authorization successful, token saved.');
        return $response;
    }
}
