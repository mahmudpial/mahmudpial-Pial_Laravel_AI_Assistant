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
            'default_model' => 'openai/gpt-oss-120b',
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
        $providerConfig = self::PROVIDERS[$provider] ?? self::PROVIDERS['groq'];

        // 1. Check setting in database (Admin panel configuration)
        $dbModel = Setting::get('ai_model');
        if (!empty($dbModel)) {
            return $dbModel;
        }

        // 2. Check provider-specific env variable (e.g. GROQ_MODEL, OPENAI_MODEL)
        $providerEnv = env(strtoupper($provider) . '_MODEL');
        if (!empty($providerEnv)) {
            return $providerEnv;
        }

        // 3. Check generic AI_MODEL env variable
        $aiModelEnv = env('AI_MODEL');
        if (!empty($aiModelEnv)) {
            return $aiModelEnv;
        }

        // 4. Fall back to provider default
        return $providerConfig['default_model'];
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
        $reasoningText = '';
        $rawErrorBody = '';

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
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) use (&$fullResponse, &$reasoningText, &$rawErrorBody, $onChunk) {
            $lines = explode("\n", $data);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                if (!str_starts_with($line, 'data:')) {
                    // Possible API error payload or raw non-SSE line
                    $rawErrorBody .= $line;
                    continue;
                }

                $jsonStr = trim(substr($line, 5));
                if ($jsonStr === '[DONE]') {
                    break;
                }

                $json = json_decode($jsonStr, true);
                if (!is_array($json)) {
                    continue;
                }

                // Check for standard delta content
                if (isset($json['choices'][0]['delta']['content'])) {
                    $token = $json['choices'][0]['delta']['content'];
                    if ($token !== '') {
                        $fullResponse .= $token;
                        $onChunk($token);
                    }
                } elseif (isset($json['choices'][0]['delta']['reasoning'])) {
                    $reasoningText .= $json['choices'][0]['delta']['reasoning'];
                }
            }
            return strlen($data);
        });

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Handle transport / curl failure
        if ($curlError && empty($fullResponse)) {
            $err = "⚠️ Connection Error: {$curlError}";
            Log::error('AiService cURL error', ['error' => $curlError]);
            $onChunk($err);
            return $err;
        }

        // Handle HTTP error (e.g. 400, 401, 404, 429)
        if ($httpCode >= 400 && empty($fullResponse)) {
            $errMsg = "API Error ({$httpCode})";
            if (!empty($rawErrorBody)) {
                $errJson = json_decode($rawErrorBody, true);
                if (!empty($errJson['error']['message'])) {
                    $errMsg .= ': ' . $errJson['error']['message'];
                } else {
                    $errMsg .= ': ' . substr($rawErrorBody, 0, 200);
                }
            }
            Log::error('AiService stream HTTP error', ['status' => $httpCode, 'body' => $rawErrorBody]);
            $onChunk("⚠️ {$errMsg}");
            return "⚠️ {$errMsg}";
        }

        // If content is empty but model produced reasoning, fallback to reasoning
        if (empty(trim($fullResponse)) && !empty(trim($reasoningText))) {
            $fullResponse = $reasoningText;
            $onChunk($fullResponse);
            return $fullResponse;
        }

        // Final sanity check for blank response
        if (empty(trim($fullResponse))) {
            $emptyMsg = "⚠️ Empty response received from {$providerConfig['name']} (model: {$model}). Please try again or check settings.";
            $onChunk($emptyMsg);
            return $emptyMsg;
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
