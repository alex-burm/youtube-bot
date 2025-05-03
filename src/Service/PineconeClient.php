<?php

namespace App\Service;

use GuzzleHttp\Client;

class PineconeClient
{
    protected Client $client;

    public function __construct(
        protected array $settings,
    ) {
        $this->client = new Client([
            'base_uri' => $this->settings['base_uri'],
            'headers' => [
                'Api-Key' => $this->settings['api_key'],
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    public function upsert(array $vectors): array
    {
        $response = $this->client->post('/vectors/upsert', [
            'json' => ['vectors' => $vectors]
        ]);
        return \json_decode($response->getBody()->getContents(), true);
    }

    public function query(array $vector, int $topK = 5): array
    {
        $response = $this->client->post('/query', [
            'json' => [
                'vector' => $vector,
                'topK' => $topK,
               // 'includeValues' => true,
                'includeMetadata' => true
            ]
        ]);
        return \json_decode($response->getBody()->getContents(), true);
    }

    public function clear(): array
    {
        $response = $this->client->post('/vectors/delete', [
            'json' => [
                'deleteAll' => true
            ]
        ]);
        return \json_decode($response->getBody()->getContents(), true);
    }
}
