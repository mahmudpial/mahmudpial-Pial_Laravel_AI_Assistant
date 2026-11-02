<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Setting;
use App\Models\User;
use App\Services\AiService;
use App\Services\QuotaTracker;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Admin Overview Dashboard.
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_conversations' => Conversation::count(),
            'total_messages' => Message::count(),
            'today_requests' => QuotaTracker::getTodayCount(),
            'remaining_quota' => QuotaTracker::getRemaining(),
            'active_provider' => AiService::getActiveProvider(),
            'active_model' => AiService::getActiveModel(),
        ];

        $recentUsers = User::latest()->take(5)->get();
        $recentConversations = Conversation::with('user')->latest('updated_at')->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentConversations'));
    }

    /**
     * User Management.
     */
    public function users()
    {
        $users = User::withCount('conversations')->latest()->paginate(15);
        return view('admin.users', compact('users'));
    }

    /**
     * Toggle User Admin Role.
     */
    public function toggleRole(int $id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own admin role.');
        }

        $user->role = ($user->role === 'admin') ? 'user' : 'admin';
        $user->save();

        return back()->with('success', "Updated role for {$user->name} to {$user->role}.");
    }

    /**
     * Settings Page.
     */
    public function settings()
    {
        $providers = AiService::PROVIDERS;
        $activeProvider = AiService::getActiveProvider();
        $activeModel = AiService::getActiveModel();
        $systemPrompt = AiService::getSystemPrompt();

        $keys = [
            'groq' => Setting::get('groq_api_key', env('GROQ_API_KEY', '')),
            'openai' => Setting::get('openai_api_key', env('OPENAI_API_KEY', '')),
            'deepseek' => Setting::get('deepseek_api_key', env('DEEPSEEK_API_KEY', '')),
            'gemini' => Setting::get('gemini_api_key', env('GEMINI_API_KEY', '')),
        ];

        return view('admin.settings', compact('providers', 'activeProvider', 'activeModel', 'systemPrompt', 'keys'));
    }

    /**
     * Save AI & System Settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'ai_provider' => 'required|string|in:groq,openai,deepseek,gemini',
            'ai_model' => 'required|string|max:100',
            'groq_api_key' => 'nullable|string',
            'openai_api_key' => 'nullable|string',
            'deepseek_api_key' => 'nullable|string',
            'gemini_api_key' => 'nullable|string',
            'system_prompt' => 'required|string|max:5000',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'AI and System Settings updated successfully!');
    }
}
