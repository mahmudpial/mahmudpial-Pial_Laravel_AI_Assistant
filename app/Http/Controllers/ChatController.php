<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{

    private const SYSTEM_PROMPT = <<<PROMPT
        You are an expert Laravel developer.
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
    public function index()
    {
        // Fetch current conversation history array, defaulting to an empty array if unset
        $history = session('chat_history', []);

        return view('chat', compact('history'));
    }

    public function send(Request $request, GeminiService $gemini): JsonResponse
    {
        // 1. Validate that the incoming text payload meets structural and security boundaries
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        // 2. Hydrate existing conversation history data from the active session
        $history = session('chat_history', []);

        // 3. Format and append the new incoming user utterance using the service's structural utility
        $history[] = GeminiService::userTurn($request->message);

        // 4. Check cache to avoid duplicate API calls for identical messages
        $cacheKey = 'gemini-response-' . hash('sha256', $request->message);
        $cachedReply = cache()->get($cacheKey);
        if ($cachedReply) {
            $replyText = $cachedReply;
        } else {
            // Dispatch the payload timeline along with system configuration to the LLM agent handler
            $replyText = $gemini->chat($history, self::SYSTEM_PROMPT);
            // Cache successful responses for 1 hour
            if (!str_contains($replyText, 'Error') && !str_contains($replyText, 'Quota')) {
                cache()->put($cacheKey, $replyText, 3600);
            }
        }

        // 5. Format and append the generated model response into the execution history array
        $history[] = GeminiService::modelTurn($replyText);

        // 6. Enforce a rolling history window. Retain only the last 20 entries to prevent 
        //    unnecessary memory allocation, session bloating, and context token ceiling blowouts.
        session(['chat_history' => array_slice($history, -20)]);

        // 7. Return the computed message chunk back to the client UI application
        return response()->json([
            'reply' => $replyText,
            'success' => true,
        ]);
    }

    public function clear(): JsonResponse
    {
        // Remove the historical conversational logs entirely from the active session store
        session()->forget('chat_history');

        return response()->json(['success' => true]);
    }
}
