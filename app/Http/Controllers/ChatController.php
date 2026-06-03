<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use App\Services\QuotaTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    private const MAX_HISTORY_TURNS = 20; // keep last 20 exchanges (40 entries)

    public function __construct(private GeminiService $gemini)
    {
    }

    /**
     * Render the main chat page.
     */
    public function index()
    {
        $history = session('chat_history', []);
        $quotaStatus = QuotaTracker::getStatus();
        $isLowQuota = QuotaTracker::isLow() || QuotaTracker::isExhausted();
        $quotaFillClass = QuotaTracker::getFillClass();

        return view('chat', compact('history', 'quotaStatus', 'isLowQuota', 'quotaFillClass'));
    }

    /**
     * Handle an incoming chat message.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        // ── 1. Daily quota check ──────────────────────────────
        if (QuotaTracker::isExhausted()) {
            return $this->quotaResponse(GeminiService::quotaErrorMessage(), 429);
        }

        // ── 2. Per-minute rate limit check ────────────────────
        if (QuotaTracker::isRpmExhausted()) {
            $rpm = QuotaTracker::getRpmRemaining(); // will be 0
            return $this->quotaResponse(
                '⏱️ Rate limit reached (30 req/min on free tier). Please wait a moment and try again.',
                429
            );
        }

        // ── 3. Record usage & send to Gemini ──────────────────
        QuotaTracker::recordRequest();

        $message = trim($request->input('message'));
        $history = session('chat_history', []);

        // Append the new user message
        $history[] = GeminiService::userTurn($message);

        // Trim to last N turns to control token usage
        if (count($history) > self::MAX_HISTORY_TURNS * 2) {
            $history = array_slice($history, -(self::MAX_HISTORY_TURNS * 2));
        }

        // Call Gemini
        $reply = $this->gemini->chat($history);

        // Append model reply to history
        $history[] = GeminiService::modelTurn($reply);

        // Persist updated history
        session(['chat_history' => $history]);

        return response()->json([
            'reply' => $reply,
            'quota' => QuotaTracker::getRemaining(),
            'quotaStatus' => QuotaTracker::getStatus(),
            'isLowQuota' => QuotaTracker::isLow() || QuotaTracker::isExhausted(),
            'rpmRemaining' => QuotaTracker::getRpmRemaining(),
        ]);
    }

    /**
     * Clear the current session's chat history.
     */
    public function clear(Request $request): JsonResponse
    {
        $request->session()->forget('chat_history');
        return response()->json(['cleared' => true]);
    }

    // ── Private helpers ───────────────────────────────────────

    private function quotaResponse(string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'reply' => $message,
            'quota' => QuotaTracker::getRemaining(),
            'quotaStatus' => QuotaTracker::getStatus(),
            'isLowQuota' => true,
            'rpmRemaining' => QuotaTracker::getRpmRemaining(),
        ], $status);
    }
}