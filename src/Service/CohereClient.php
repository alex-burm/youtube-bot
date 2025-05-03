<?php

namespace App\Service;

use GuzzleHttp\Client;

class CohereClient
{
    private Client $httpClient;
    private string $apiKey;

    public function __construct(
        protected array $settings
    ) {
        $this->apiKey = $this->settings['api_key'];
        $this->httpClient = new Client([
            'base_uri' => $this->settings['base_uri'],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    public function embed(string $text): array
    {
        $response = $this->httpClient->post('/embed', [
            'json' => [
                'texts' => [$text],
                'input_type' => 'search_document',
                'model' => 'embed-multilingual-v3.0',
                'truncate' => 'NONE',
            ]
        ]);

        $data = \json_decode($response->getBody()->getContents(), true);
        return \current($data['embeddings'] ?? []);
    }
}
