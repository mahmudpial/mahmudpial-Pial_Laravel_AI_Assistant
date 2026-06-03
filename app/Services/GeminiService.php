<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class GeminiService
{
    private string $apiKey;

    private string $model;

    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    /**
     * GeminiService constructor.
     * Pulls necessary API configurations and fallbacks from Laravel service config files.
     */
    public function __construct()
    {
        $this->apiKey = (string) config('services.gemini.key');
        $this->model = (string) config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Send a message payload containing full conversational history thread context.
     *
     * @param  array   $history       Array of steps: [['role' => 'user'|'model', 'parts' => [['text' => '...']]]]
     * @param  string  $systemPrompt  Optional behavioral rules or persona context for the model
     * @return string                 The model's text response or an explicit error message
     */
    public function chat(array $history, string $systemPrompt = ''): string
    {
        if (blank($this->apiKey)) {
            Log::warning('Gemini API key is missing; skipping remote chat request.');

            return 'The AI chat service is not configured yet. Add GEMINI_API_KEY to your .env file to enable live replies.';
        }

        $contents = [];

        foreach ($history as $turn) {
            $contents[] = [
                'role' => $turn['role'] === 'model' ? 'model' : 'user',
                'parts' => [
                    ['text' => $turn['parts'][0]['text']],
                ],
            ];
        }

        try {
            $payload = [
                'contents' => $contents,
            ];

            if (!empty($systemPrompt)) {
                $payload['system_instruction'] = [
                    'parts' => [
                        ['text' => $systemPrompt],
                    ],
                ];
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'x-goog-api-key' => $this->apiKey,
                ])
                ->post($this->baseUrl . '/' . $this->model . ':generateContent', $payload)
                ->throw();

            return $response->json('candidates.0.content.parts.0.text')
                ?? 'Error: Empty response content received from Gemini.';

        } catch (RequestException $e) {
            Log::error('Gemini API Request Exception', [
                'status' => $e->response->status(),
                'body' => $e->response->body()
            ]);

            // Handle quota exceeded error specifically
            $errorMsg = $e->response->json('error.message', 'An unknown API error occurred.');
            if (str_contains($errorMsg, 'quota') || str_contains($errorMsg, 'Quota')) {
                return '❌ API Quota Exceeded. Free tier limits:' . "\n"
                    . '• 10 requests per minute' . "\n"
                    . '• 250 requests per day' . "\n"
                    . '• 250,000 tokens per minute' . "\n\n"
                    . '**Option 1: Upgrade to Paid Plan**' . "\n"
                    . 'Visit https://ai.google.dev/pricing to upgrade and get 15,000 requests/minute.' . "\n\n"
                    . '**Option 2: Wait for Reset**' . "\n"
                    . 'Daily quota resets at midnight UTC. Try again tomorrow.';
            }

            return 'API Error: ' . $errorMsg;

        } catch (\Throwable $e) {
            // Catch and log unexpected structural breakdowns or connection terminations
            Log::error('GeminiService Critical Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 'Internal Server Error: Unable to process your request at this time.';
        }
    }

    /**
     * Standardizes a user chat node into a valid Gemini 'contents' array segment.
     *
     * @param  string  $text  The user message string
     * @return array          Gemini formatted user chat segment
     */
    public static function userTurn(string $text): array
    {
        return ['role' => 'user', 'parts' => [['text' => $text]]];
    }

    /**
     * Standardizes an assistant/model chat node into a valid Gemini 'contents' array segment.
     *
     * @param  string  $text  The model output string
     * @return array          Gemini formatted model chat segment
     */
    public static function modelTurn(string $text): array
    {
        return ['role' => 'model', 'parts' => [['text' => $text]]];
    }
}
