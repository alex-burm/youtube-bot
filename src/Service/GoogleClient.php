<?php

namespace App\Service;

use Google\Client;
use Google\Service\YouTube;

class GoogleClient
{
    protected Client $client;

    public function __construct(
        protected string $credentialsPath,
        protected string $tokenPath,
        protected string $notifyEmail
    ) {
        $client = new Client();
        $client->setAuthConfig($this->credentialsPath);
        $client->addScope(YouTube::YOUTUBE_FORCE_SSL);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $this->client = $client;
    }

    public function getClient(): Client
    {
        $this->loadToken($this->client);
        return $this->client;
    }

    public function getYoutube(): YouTube
    {
        return new YouTube($this->getClient());
    }

    protected function loadToken(Client $client): void
    {
        if (false === \file_exists($this->tokenPath)) {
            $this->notifyTokenIssue('No token file found.');
            return ;
        }
        try {
            $client->setAccessToken(
                \json_decode(
                    \file_get_contents($this->tokenPath),
                    true
                )
            );

            if (false === $client->isAccessTokenExpired()) {
                return;
            }

            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                \file_put_contents($this->tokenPath, \json_encode($client->getAccessToken()));
            } else {
                \unlink($this->tokenPath);
                $this->notifyTokenIssue('Token expired and no refresh token.');
            }
        } catch (\Exception $e) {
            unlink($this->tokenPath);
            $this->notifyTokenIssue('Error loading token: ' . $e->getMessage());
        }
    }

    protected function notifyTokenIssue(string $message): void
    {
        //\mail($this->notifyEmail, 'OAuth Token Issue', $message);
    }
}
