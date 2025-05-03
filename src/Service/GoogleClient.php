<?php

namespace App\Service;

use Google\Client;
use Google\Service\YouTube;

class GoogleClient
{
    protected Client $client;

    public function __construct(
        protected array $settings,
    ) {
        $client = new Client();
        $client->setAuthConfig($this->settings['credentials_path']);
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
        if (false === \file_exists($this->settings['token_path'])) {
            $this->notifyTokenIssue('No token file found.');
            return ;
        }
        try {
            $client->setAccessToken(
                \json_decode(
                    \file_get_contents($this->settings['token_path']),
                    true
                )
            );

            if (false === $client->isAccessTokenExpired()) {
                return;
            }

            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                \file_put_contents($this->settings['token_path'], \json_encode($client->getAccessToken()));
            } else {
                \unlink($this->settings['token_path']);
                $this->notifyTokenIssue('Token expired and no refresh token.');
            }
        } catch (\Exception $e) {
            \unlink($this->settings['token_path']);
            $this->notifyTokenIssue('Error loading token: ' . $e->getMessage());
        }
    }

    protected function notifyTokenIssue(string $message): void
    {
        //\mail($this->settings['notify_email'], 'OAuth Token Issue', $message);
    }
}
