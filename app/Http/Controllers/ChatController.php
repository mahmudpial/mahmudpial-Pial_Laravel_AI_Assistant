<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\AiService;
use App\Services\GroqService;
use App\Services\QuotaTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    private const MAX_HISTORY_TURNS = 20;

    public function __construct(private AiService $ai, private GroqService $groq)
    {
    }

    /**
     * Render the main chat page (new conversation).
     */
    public function index()
    {
        $userId = Auth::id();
        $groupedConversations = $this->getGroupedConversations($userId);

        $activeConversation = null;
        $history = [];

        $quotaStatus = QuotaTracker::getStatus();
        $isLowQuota = QuotaTracker::isLow() || QuotaTracker::isExhausted();
        $quotaFillClass = QuotaTracker::getFillClass();

        return view('chat', compact(
            'history',
            'groupedConversations',
            'activeConversation',
            'quotaStatus',
            'isLowQuota',
            'quotaFillClass'
        ));
    }

    /**
     * Render a specific conversation by ID.
     */
    public function show(int $id)
    {
        $userId = Auth::id();
        $conversation = Conversation::where('user_id', $userId)
            ->with(['messages'])
            ->findOrFail($id);

        $groupedConversations = $this->getGroupedConversations($userId);
        $activeConversation = $conversation;

        // Convert messages to history turns for the view
        $history = [];
        foreach ($conversation->messages as $msg) {
            $history[] = [
                'role' => $msg->role,
                'parts' => [['text' => $msg->content]],
                'id' => $msg->id,
                'created_at' => $msg->created_at->diffForHumans(),
            ];
        }

        $quotaStatus = QuotaTracker::getStatus();
        $isLowQuota = QuotaTracker::isLow() || QuotaTracker::isExhausted();
        $quotaFillClass = QuotaTracker::getFillClass();

        return view('chat', compact(
            'history',
            'groupedConversations',
            'activeConversation',
            'quotaStatus',
            'isLowQuota',
            'quotaFillClass'
        ));
    }

    /**
     * Handle sending a message.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:4000',
            'conversation_id' => 'nullable|integer',
        ]);

        if (QuotaTracker::isExhausted()) {
            return $this->quotaResponse('❌ Daily quota exceeded. Please try again tomorrow.', 429);
        }

        if (QuotaTracker::isRpmExhausted()) {
            return $this->quotaResponse('⏱️ Rate limit reached. Please wait a moment and try again.', 429);
        }

        QuotaTracker::recordRequest();

        $userId = Auth::id();
        $messageText = trim($request->input('message'));
        $conversationId = $request->input('conversation_id');

        // Resolve or create conversation
        if ($conversationId) {
            $conversation = Conversation::where('user_id', $userId)->findOrFail($conversationId);
        } else {
            // Auto generate title from first prompt
            $title = Str::limit($messageText, 36, '...');
            $conversation = Conversation::create([
                'user_id' => $userId,
                'title' => $title,
            ]);
        }

        // Store user message in DB
        Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $messageText,
        ]);

        // Build history array for AI service
        $messages = $conversation->messages()->oldest()->take(self::MAX_HISTORY_TURNS * 2)->get();
        $history = [];
        foreach ($messages as $msg) {
            $history[] = [
                'role' => $msg->role === 'ai' ? 'model' : 'user',
                'parts' => [['text' => $msg->content]],
            ];
        }

        // Call AI Service
        $reply = $this->ai->chat($history);

        // Store AI reply in DB
        Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'ai',
            'content' => $reply,
        ]);

        $conversation->touch();

        return response()->json([
            'reply' => $reply,
            'conversation_id' => $conversation->id,
            'conversation_title' => $conversation->title,
            'quota' => QuotaTracker::getRemaining(),
            'quotaStatus' => QuotaTracker::getStatus(),
            'isLowQuota' => QuotaTracker::isLow() || QuotaTracker::isExhausted(),
            'rpmRemaining' => QuotaTracker::getRpmRemaining(),
            'quotaPercent' => QuotaTracker::getRemainingPercent(),
        ]);
    }

    /**
     * Stream incoming chat message via Server-Sent Events (SSE).
     */
    public function stream(Request $request): StreamedResponse|JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:4000',
            'conversation_id' => 'nullable|integer',
        ]);

        if (QuotaTracker::isExhausted()) {
            return response()->json(['error' => '❌ Daily quota exceeded.'], 429);
        }

        if (QuotaTracker::isRpmExhausted()) {
            return response()->json(['error' => '⏱️ Rate limit reached.'], 429);
        }

        QuotaTracker::recordRequest();

        $userId = Auth::id();
        $messageText = trim($request->input('message'));
        $conversationId = $request->input('conversation_id');

        if ($conversationId) {
            $conversation = Conversation::where('user_id', $userId)->findOrFail($conversationId);
        } else {
            $title = Str::limit($messageText, 36, '...');
            $conversation = Conversation::create([
                'user_id' => $userId,
                'title' => $title,
            ]);
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $messageText,
        ]);

        $messages = $conversation->messages()->oldest()->take(self::MAX_HISTORY_TURNS * 2)->get();
        $history = [];
        foreach ($messages as $msg) {
            $history[] = [
                'role' => $msg->role === 'ai' ? 'model' : 'user',
                'parts' => [['text' => $msg->content]],
            ];
        }

        $aiService = $this->ai;

        return new StreamedResponse(function () use ($conversation, $history, $aiService) {
            if (ob_get_level()) ob_end_clean();

            // Emit meta header
            echo "data: " . json_encode([
                'type' => 'meta',
                'conversation_id' => $conversation->id,
                'conversation_title' => $conversation->title,
            ]) . "\n\n";
            flush();

            // Stream chunks
            $fullReply = $aiService->stream($history, function ($token) {
                echo "data: " . json_encode(['type' => 'chunk', 'content' => $token]) . "\n\n";
                flush();
            });

            // Persist full reply in DB
            Message::create([
                'conversation_id' => $conversation->id,
                'role' => 'ai',
                'content' => $fullReply,
            ]);
            $conversation->touch();

            // Emit completion
            echo "data: " . json_encode([
                'type' => 'done',
                'quota' => QuotaTracker::getRemaining(),
                'quotaStatus' => QuotaTracker::getStatus(),
                'quotaPercent' => QuotaTracker::getRemainingPercent(),
            ]) . "\n\n";
            flush();
        }, 200, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Rename conversation title.
     */
    public function rename(Request $request, int $id): JsonResponse
    {
        $request->validate(['title' => 'required|string|max:60']);
        $conversation = Conversation::where('user_id', Auth::id())->findOrFail($id);
        $conversation->update(['title' => trim($request->input('title'))]);

        return response()->json(['success' => true, 'title' => $conversation->title]);
    }

    /**
     * Delete a conversation.
     */
    public function destroy(int $id): JsonResponse
    {
        $conversation = Conversation::where('user_id', Auth::id())->findOrFail($id);
        $conversation->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Clear / start fresh conversation.
     */
    public function clear(Request $request): JsonResponse
    {
        $request->session()->forget('chat_history');
        return response()->json(['cleared' => true]);
    }

    /**
     * Get list of conversations for sidebar AJAX update.
     */
    public function conversations(): JsonResponse
    {
        return response()->json($this->getGroupedConversations(Auth::id()));
    }

    // ── Private helpers ───────────────────────────────────────

    private function getGroupedConversations(int $userId): array
    {
        $all = Conversation::where('user_id', $userId)
            ->latest('updated_at')
            ->get();

        $today = [];
        $previous7Days = [];
        $older = [];

        $now = now();
        $startOfToday = $now->copy()->startOfDay();
        $sevenDaysAgo = $now->copy()->subDays(7)->startOfDay();

        foreach ($all as $c) {
            $item = [
                'id' => $c->id,
                'title' => $c->title,
                'updated_at' => $c->updated_at->diffForHumans(),
            ];

            if ($c->updated_at >= $startOfToday) {
                $today[] = $item;
            } elseif ($c->updated_at >= $sevenDaysAgo) {
                $previous7Days[] = $item;
            } else {
                $older[] = $item;
            }
        }

        return [
            'today' => $today,
            'previous7Days' => $previous7Days,
            'older' => $older,
            'total' => $all->count(),
        ];
    }

    private function quotaResponse(string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'reply' => $message,
            'quota' => QuotaTracker::getRemaining(),
            'quotaStatus' => QuotaTracker::getStatus(),
            'isLowQuota' => true,
            'rpmRemaining' => QuotaTracker::getRpmRemaining(),
            'quotaPercent' => QuotaTracker::getRemainingPercent(),
        ], $status);
    }
}
