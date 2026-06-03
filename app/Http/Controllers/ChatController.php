<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use App\Services\QuotaTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    private const SYSTEM_PROMPT = <<<PROMPT
        You are an expert Laravel developer and helpful AI assistant.
        Always respond clearly and professionally in English.
        Your answers must be easy to scan and must separate explanation from implementation.
        When code is needed, use this exact template and keep the sections in this order:

        Explanation
        Give a short, direct explanation in plain language.

        Code
        Put implementation code in fenced code blocks with the correct language.

        Output
        Show the expected result, behavior, or example output only if it helps understanding.

        Notes
        Add brief follow-up notes only when they are genuinely useful.

        Use this exact order whenever code is involved: Explanation, Code, Output, Notes.
        If the answer does not need code, skip Code and Output.
        Keep explanations concise, practical, and beginner-friendly.
        Never mix long explanations inside code blocks.
        PROMPT;

    /**
     * Get the cache key for the current user's chat history.
     */
    private function getCacheKey(): string
    {
        return 'chat_history_' . auth()->id();
    }

    /**
     * Show the chat page.
     */
    public function index()
    {
        $history = cache()->get($this->getCacheKey(), []);
        $quotaRemaining = QuotaTracker::getRemaining();
        $quotaStatus = QuotaTracker::getStatus();
        $isLowQuota = QuotaTracker::isLow();
        
        return view('chat', compact('history', 'quotaRemaining', 'quotaStatus', 'isLowQuota'));
    }

    /**
     * Send a message and get a response.
     */
    public function send(Request $request, GeminiService $gemini): JsonResponse
    {
        // Validate input
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        // Get user's chat history
        $history = cache()->get($this->getCacheKey(), []);

        // Add user message
        $history[] = GeminiService::userTurn($request->message);

        // Check cache for identical message FIRST (doesn't count toward rate limit)
        $cacheKey = 'gemini-response-' . hash('sha256', $request->message . auth()->id());
        $cachedReply = cache()->get($cacheKey);

        if ($cachedReply) {
            // Cached response - doesn't use rate limit
            $replyText = $cachedReply;
        } else {
            // New API call - check rate limit ONLY for actual API calls
            $rateLimitKey = 'gemini-api-' . auth()->id();
            if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
                $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($rateLimitKey);
                return response()->json([
                    'reply' => "⏳ Rate limited. Please wait {$seconds} seconds before sending another message.",
                    'success' => false,
                    'quota' => QuotaTracker::getRemaining(),
                    'quotaStatus' => QuotaTracker::getStatus(),
                    'isLowQuota' => QuotaTracker::isLow(),
                ]);
            }

            // Hit rate limit only for actual API calls
            \Illuminate\Support\Facades\RateLimiter::hit($rateLimitKey, 60);

            // Get response from Gemini
            $replyText = $gemini->chat($history, self::SYSTEM_PROMPT);

            // Track the API request
            QuotaTracker::recordRequest();

            // Cache successful responses for 1 hour
            if (!str_contains($replyText, 'Error') && !str_contains($replyText, 'Quota')) {
                cache()->put($cacheKey, $replyText, 3600);
            }
        }

        // Add assistant response
        $history[] = GeminiService::modelTurn($replyText);

        // Keep last 20 messages
        $history = array_slice($history, -20);

        // Store in cache for 7 days
        cache()->put($this->getCacheKey(), $history, 604800);

        return response()->json([
            'reply' => $replyText,
            'success' => true,
            'quota' => QuotaTracker::getRemaining(),
            'quotaStatus' => QuotaTracker::getStatus(),
            'isLowQuota' => QuotaTracker::isLow(),
        ]);
    }

    /**
     * Clear chat history.
     */
    public function clear(): JsonResponse
    {
        cache()->forget($this->getCacheKey());
        return response()->json(['success' => true]);
    }
}
