<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    public const PROVIDERS = [
        'groq' => [
            'name' => 'Groq (Ultra-Fast LPU)',
            'endpoint' => 'https://api.groq.com/openai/v1/chat/completions',
            'default_model' => 'llama-3.3-70b-versatile',
            'env_key' => 'GROQ_API_KEY',
        ],
        'openai' => [
            'name' => 'OpenAI',
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
            'default_model' => 'gpt-4o-mini',
            'env_key' => 'OPENAI_API_KEY',
        ],
        'deepseek' => [
            'name' => 'DeepSeek',
            'endpoint' => 'https://api.deepseek.com/chat/completions',
            'default_model' => 'deepseek-chat',
            'env_key' => 'DEEPSEEK_API_KEY',
        ],
        'gemini' => [
            'name' => 'Google Gemini (OpenAI Endpoint)',
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions',
            'default_model' => 'gemini-1.5-flash',
            'env_key' => 'GEMINI_API_KEY',
        ],
    ];

    private const DEFAULT_SYSTEM_PROMPT = <<<PROMPT
You are an expert Laravel and Web Engineering assistant built by Pial Mahmud.
Role:
- Answer questions with clean, production-ready code examples (Laravel 11, PHP 8+, Eloquent, APIs, Vue/React, Pest).
- Provide concise, developer-friendly, and secure solutions.
- Format all code with markdown syntax highlighting tags.
PROMPT;

    public static function getActiveProvider(): string
    {
        return Setting::get('ai_provider', env('AI_PROVIDER', 'groq'));
    }

    public static function getActiveModel(): string
    {
        $provider = self::getActiveProvider();
        $defaultModel = self::PROVIDERS[$provider]['default_model'] ?? 'llama-3.3-70b-versatile';
        return Setting::get('ai_model', env('AI_MODEL', $defaultModel));
    }

    public static function getApiKey(string $provider): string
    {
        $envKey = self::PROVIDERS[$provider]['env_key'] ?? '';
        return Setting::get("{$provider}_api_key", env($envKey, ''));
    }

    public static function getSystemPrompt(): string
    {
        return Setting::get('system_prompt', self::DEFAULT_SYSTEM_PROMPT);
    }

    /**
     * Send standard synchronous chat request.
     */
    public function chat(array $history): string
    {
        $provider = self::getActiveProvider();
        $providerConfig = self::PROVIDERS[$provider] ?? self::PROVIDERS['groq'];
        $apiKey = self::getApiKey($provider);
        $model = self::getActiveModel();

        if (blank($apiKey)) {
            return "⚠️ The API key for {$providerConfig['name']} is missing. Please configure it in settings or the Admin Panel.";
        }

        $messages = $this->buildMessagesArray($history);

        try {
            $response = Http::timeout(45)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($providerConfig['endpoint'], [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 2048,
                ]);

            if ($response->failed()) {
                $status = $response->status();
                $err = $response->json('error.message', 'Unknown API error');
                return "API Error ({$status}): {$err}";
            }

            return $response->json('choices.0.message.content') ?? 'Empty response received from AI model.';

        } catch (\Throwable $e) {
            Log::error('AiService chat exception', ['error' => $e->getMessage()]);
            return 'Server error contacting AI service: ' . $e->getMessage();
        }
    }

    /**
     * Stream response token by token via Server-Sent Events (SSE).
     */
    public function stream(array $history, callable $onChunk): string
    {
        $provider = self::getActiveProvider();
        $providerConfig = self::PROVIDERS[$provider] ?? self::PROVIDERS['groq'];
        $apiKey = self::getApiKey($provider);
        $model = self::getActiveModel();

        if (blank($apiKey)) {
            $err = "⚠️ The API key for {$providerConfig['name']} is missing. Please configure it in the Admin Panel.";
            $onChunk($err);
            return $err;
        }

        $messages = $this->buildMessagesArray($history);
        $fullResponse = '';

        $payload = json_encode([
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 2048,
            'stream' => true,
        ]);

        $ch = curl_init($providerConfig['endpoint']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Accept: text/event-stream',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        // Process incoming SSE stream chunks
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) use (&$fullResponse, $onChunk) {
            $lines = explode("\n", $data);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || !str_starts_with($line, 'data:')) {
                    continue;
                }

                $jsonStr = trim(substr($line, 5));
                if ($jsonStr === '[DONE]') {
                    break;
                }

                $json = json_decode($jsonStr, true);
                if (isset($json['choices'][0]['delta']['content'])) {
                    $token = $json['choices'][0]['delta']['content'];
                    $fullResponse .= $token;
                    $onChunk($token);
                }
            }
            return strlen($data);
        });

        curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError && empty($fullResponse)) {
            $err = "Stream Connection Error: {$curlError}";
            $onChunk($err);
            return $err;
        }

        return $fullResponse;
    }

    private function buildMessagesArray(array $history): array
    {
        $messages = [];
        $messages[] = ['role' => 'system', 'content' => self::getSystemPrompt()];

        foreach ($history as $turn) {
            $messages[] = [
                'role' => $turn['role'] === 'model' || $turn['role'] === 'ai' ? 'assistant' : 'user',
                'content' => $turn['parts'][0]['text'] ?? '',
            ];
        }

        return $messages;
    }
}
