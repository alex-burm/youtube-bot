<?php

namespace App\Service;

use OpenAI\Client;

class GptClient
{
    protected Client $client;

    public function __construct(
        protected array $settings,
    ) {
        $this->client = \OpenAI::client($this->settings['api_key']);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function embed(string $prompt): array
    {
        $response = $this->client->embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => $prompt,
        ]);

        return $response->embeddings[0]->embedding;
    }

    public function summarize(string $message): string
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-4-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => <<<EOT
Ты — помощник, делающий краткое, но насыщенное по смыслу резюме по транскриптам технических видео (программирование, фреймворки, API). Проанализируй весь текст и ответь кратко:

1. Какие темы раскрываются?
2. Какие действия выполняются (настройка, написание кода и т.д.)?
3. Какие инструменты, библиотеки, фреймворки или методы упоминаются?
4. Какие ошибки или важные нюансы объяснены?

Избегай приветствий, заключений, лишних эмоций. Пиши в стиле справки или техдокументации.

Результат должен быть коротким и пригодным для индексации в векторную базу.
EOT
                ],
                [
                    'role' => 'user',
                    'content' => <<<EOT
Вот транскрипт видео. 
Составь краткое, ёмкое и точное саммари, отражающее его основную тему, суть и пользу для зрителя. 
Пиши от 2 до 5 предложений, не упоминай автора, не пересказывай, а именно резюмируй суть.

{$message}
EOT,
                ]
            ],
        ]);

        return $response->choices[0]->message->content ?? '';
    }

    public function normalize(string $message): string
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-4-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Ты — помощник, который уточняет смысл кратких пользовательских поисковых запросов и превращает их в более подробные и смысловые. Задача — сделать запрос максимально понятным, чтобы по нему можно было эффективно искать по смыслу в векторной базе. Не добавляй приветствий и эмоций, используй русский язык для нормализации.'
                ],
                [
                    'role' => 'user',
                    'content' => $message,
                ]
            ],
        ]);

        return $response->choices[0]->message->content ?? '';
    }
}
