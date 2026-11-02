<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class InstallerController extends Controller
{
    private function isInstalled(): bool
    {
        return file_exists(storage_path('installed'));
    }

    /**
     * Step 1: Server Requirements & Permissions Check.
     */
    public function step1()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        $requirements = [
            'PHP Version (>= 8.2)' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'cURL Extension' => extension_loaded('curl'),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'PDO Extension' => extension_loaded('pdo'),
            'Mbstring Extension' => extension_loaded('mbstring'),
            'Tokenizer Extension' => extension_loaded('tokenizer'),
            'JSON Extension' => extension_loaded('json'),
            'BCMath Extension' => extension_loaded('bcmath'),
        ];

        $permissions = [
            'storage/ (writable)' => is_writable(storage_path()),
            'storage/logs/ (writable)' => is_writable(storage_path('logs')),
            'bootstrap/cache/ (writable)' => is_writable(base_path('bootstrap/cache')),
        ];

        $allOk = !in_array(false, $requirements, true) && !in_array(false, $permissions, true);

        return view('installer.step1', compact('requirements', 'permissions', 'allOk'));
    }

    /**
     * Step 2: Database Setup & Migration.
     */
    public function step2()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return view('installer.step2');
    }

    public function processStep2(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        try {
            // Run database migrations
            Artisan::call('migrate', ['--force' => true]);
            return redirect()->route('install.step3')->with('success', 'Database tables migrated successfully!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Database migration failed: ' . $e->getMessage());
        }
    }

    /**
     * Step 3: Admin User Creation & API Key.
     */
    public function step3()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return view('installer.step3');
    }

    public function processStep3(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'ai_provider' => 'required|string|in:groq,openai,deepseek,gemini',
            'api_key' => 'nullable|string',
        ]);

        // Create Admin User
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
        ]);

        // Save AI settings
        Setting::set('ai_provider', $validated['ai_provider']);
        if (!empty($validated['api_key'])) {
            Setting::set("{$validated['ai_provider']}_api_key", $validated['api_key']);
        }

        // Generate App Key if empty
        if (empty(env('APP_KEY'))) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        // Create the installed lock file
        File::put(storage_path('installed'), 'Installed on ' . now()->toIso8601String());

        return redirect()->route('install.finish');
    }

    /**
     * Finish Screen.
     */
    public function finish()
    {
        return view('installer.finish');
    }
}
