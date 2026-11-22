<?php

namespace App\Services\Generation;

use App\Contracts\StoryTextGenerator;
use App\Exceptions\StoryGenerationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Groq és Mistral: mindkettő az OpenAI chat completions formátumát fogadja, ezért egy osztály elég (Tech Stack D-16).
 * A Laravel Http kliensével hív, SDK nélkül; a kulcs csak a szerveren van (US-07 AC10), és naplóba nem kerül.
 *
 * Groq and Mistral both accept the OpenAI chat completions format, so one class is enough (Tech Stack D-16).
 * It calls with Laravel's Http client, no SDK; the key stays on the server (US-07 AC10) and is never logged.
 */
class OpenAiCompatibleStoryGenerator implements StoryTextGenerator
{
    private string $baseUrl;
    private ?string $apiKey;
    private string $model;

    public function __construct(array $provider)
    {
        $this->baseUrl = rtrim($provider['base_url'], '/');
        $this->apiKey = $provider['api_key'];
        $this->model = $provider['model'];
    }

    public function generate(array $story): string
    {
        if (empty($this->apiKey)) {
            throw new StoryGenerationException(StoryGenerationException::UNAVAILABLE, 'API key is not configured.');
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->acceptJson()
                ->timeout((int) config('story.ai_timeout'))
                ->post($this->baseUrl.'/chat/completions', [
                    'model' => $this->model,
                    'temperature' => 0.8,
                    // JSON-mód, ha a modell támogatja; a választ ettől függetlenül ellenőrizzük (Tech Stack 5.2).
                    // JSON mode when supported; the answer is validated regardless (Tech Stack 5.2).
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => StoryPrompt::system()],
                        ['role' => 'user', 'content' => StoryPrompt::user($story)],
                    ],
                ]);
        } catch (ConnectionException $e) {
            throw new StoryGenerationException(StoryGenerationException::UNAVAILABLE, 'Connection failed or timed out.');
        }

        // 429 = kvóta, 5xx = szolgáltatói hiba: nincs értelme újrapróbálni, barátságos üzenet megy (US-07 AC6).
        // 429 = quota, 5xx = provider error: no point retrying, a friendly message is shown (US-07 AC6).
        if ($response->failed()) {
            throw new StoryGenerationException(StoryGenerationException::UNAVAILABLE, 'Provider returned HTTP '.$response->status().'.');
        }

        $content = $response->json('choices.0.message.content');
        if (! is_string($content)) {
            throw new StoryGenerationException(StoryGenerationException::INVALID_RESPONSE, 'No message content.');
        }

        return $content;
    }
}
