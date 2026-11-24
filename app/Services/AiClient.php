<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiUnavailable extends \RuntimeException
{
}

/**
 * Thin Gemini wrapper. The key never lives in the repo: it comes from
 * GEMINI_API_KEY (see .env.example).
 */
class AiClient
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $model,
        private readonly int $timeout = 60,
    ) {
    }

    public static function fromConfig(): self
    {
        return new self(
            config('services.gemini.key'),
            config('services.gemini.model', 'gemini-2.0-flash'),
            (int) config('services.gemini.timeout', 60),
        );
    }

    public function model(): string
    {
        return $this->model;
    }

    /**
     * Ask the model for JSON and return the decoded array.
     *
     * @throws AiUnavailable when the provider is unreachable/misconfigured after retries
     */
    public function json(string $prompt, string $system = ''): array
    {
        if (! $this->apiKey) {
            throw new AiUnavailable('GEMINI_API_KEY is not configured.');
        }

        $payload = [
            'contents' => [[
                'role' => 'user',
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => [
                'temperature' => 0.4,
                'responseMimeType' => 'application/json',
                'maxOutputTokens' => 4096,
            ],
        ];

        if ($system !== '') {
            $payload['systemInstruction'] = ['parts' => [['text' => $system]]];
        }

        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
            $this->model
        );

        $lastError = null;

        // Two quick retries: transient 429/5xx should not kill the request.
        foreach ([0, 1] as $attempt) {
            if ($attempt > 0) {
                usleep(1_200_000);
            }

            try {
                $response = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
                    ->timeout($this->timeout)
                    ->post($url, $payload);

                if ($response->successful()) {
                    $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

                    if (! is_string($text) || trim($text) === '') {
                        $lastError = 'Empty model response';
                        continue;
                    }

                    $decoded = json_decode($this->stripFences($text), true);

                    if (! is_array($decoded)) {
                        $lastError = 'Model did not return valid JSON';
                        continue;
                    }

                    return $decoded;
                }

                $lastError = 'HTTP ' . $response->status() . ': ' . substr($response->body(), 0, 300);

                if (! in_array($response->status(), [429, 500, 502, 503, 504], true)) {
                    break;
                }
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
            }
        }

        Log::warning('AI call failed', ['error' => $lastError]);

        throw new AiUnavailable($lastError ?: 'AI service unreachable');
    }

    private function stripFences(string $text): string
    {
        $text = trim($text);

        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```[a-zA-Z]*\s*/', '', $text);
            $text = preg_replace('/```\s*$/', '', $text);
        }

        return trim((string) $text);
    }
}
