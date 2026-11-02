# 🚀 Pial Mahmud - Laravel AI Assistant (Enterprise Edition)

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.3%20%7C%208.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![TailwindCSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Pest](https://img.shields.io/badge/Tests-Pest%20PHP%20(100%25)-56B429?style=for-the-badge&logo=php&logoColor=white)](https://pestphp.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

An enterprise-grade, commercial-ready Laravel AI Assistant application powered by high-performance multi-provider LLMs (**Groq LPU**, **OpenAI GPT-4o**, **DeepSeek**, and **Google Gemini**). Built with a ChatGPT/Claude-inspired sleek workspace, real-time Server-Sent Events (SSE) streaming, specialized developer personas, instant stream interruption, conversation persistence, comprehensive export capabilities, and an administrative control panel.

---

## 🔑 Demo Login Credentials

After seeding the database (`php artisan db:seed`), use the following pre-configured credentials to test all user and administrative features:

| Role | Email | Password | Access Level |
| :--- | :--- | :--- | :--- |
| **👑 Administrator** | `admin@example.com` | `password` | Full Admin Panel (`/admin/settings`), API Key Management, Model Selector, Live Connection Tester |
| **🧑 Standard / Demo User** | `test@example.com` | `password` | AI Chatbot Workspace, All 6 AI Personas, History & Export Tools |

---

## ✨ Key Features & How They Work

### 1. 🧠 Multi-Provider AI Engine (Groq, OpenAI, DeepSeek, Gemini)
* **How it works**: Powered by `App\Services\AiService`. The app supports on-the-fly provider switching without touching application code. Providers can be configured either via `.env` or dynamically through the database in the Admin Settings panel.
* **Supported Engines**:
  * **Groq**: Ultra-fast inference running on LPUs (`openai/gpt-oss-120b`, `llama-3.3-70b-versatile`).
  * **OpenAI**: State-of-the-art models (`gpt-4o`, `gpt-4o-mini`).
  * **DeepSeek**: Code reasoning and deep analysis (`deepseek-chat`, `deepseek-reasoner`).
  * **Google Gemini**: High-context multilingual generation (`gemini-1.5-flash`, `gemini-1.5-pro`).

### 2. ⚡ Real-Time SSE Token Streaming
* **How it works**: Client-server communication is built on Server-Sent Events (SSE) via `/chat/stream`. Responses are streamed token-by-token directly using PHP `flush()` and native browser `fetch` ReadableStreams.
* **Formatting**: Incoming chunks are parsed dynamically in real-time via `marked.js` with syntax highlighting using `highlight.js`, complete with one-click copy buttons on all code blocks.

### 3. 🎭 Specialized AI Expert Personas
* **How it works**: Choose tailored developer personas from the top header dropdown or input indicator. Each persona injects custom system instructions into the model prompt:
  1. **Laravel Architect**: Clean architecture, DDD, repository/service pattern, SOLID principles.
  2. **Bug & Error Debugger**: Stack trace inspection, root-cause diagnosis, and copy-paste code fixes.
  3. **Test Specialist (Pest/PHPUnit)**: Author expressive unit tests, feature tests, and mocked service assertions.
  4. **SQL & Query Optimizer**: Eliminate N+1 queries, design composite indexes, and tune Eloquent relationships.
  5. **Tailwind & UI/UX Specialist**: Modern Blade components, responsive UI, micro-animations, and accessibility.
  6. **Security & Pentest Auditor**: OWASP Top 10, Sanctum token security, CSRF, XSS, and RBAC vulnerability audits.

### 4. ⏹️ Stop Generation & 🔄 Regenerate Response (ChatGPT/Claude Style)
* **Floating Stop Button in Chat Section**: While a response is streaming, a floating glassmorphic pill button **`[ ⏹ Stop generating ]`** appears in the chat area above the input bar.
* **Morphing Send Button**: The input bar's send button (`↑`) morphs into a solid square (`■`) during streaming.
* **Keyboard Shortcut**: Pressing `Escape` at any time instantly halts generation.
* **Graceful Cancellation**: Stream interruption calls `reader.cancel()` and `AbortController.abort()`. Any partial response generated up to that moment is preserved in the chat with action buttons.
* **Regenerate**: Every AI bubble includes a **Regenerate** action button to re-run the previous prompt in one click.

### 5. 📄 Comprehensive Chat Export Suite
* **Export as Markdown (`.md`)**: Downloads a clean, formatted Markdown document containing the entire chat history.
* **Print / Save as PDF**: Formatted `@media print` layout that hides all sidebars, headers, and buttons, rendering a clean, printable transcript.
* **Copy Full Chat**: Instantly copies the full conversation transcript to the system clipboard.

### 6. 🗄️ Persistent Conversations & Sidebar Management
* **Database Driven**: Conversations and messages are saved in the database with user ownership.
* **Grouped Timeframes**: Automatically groups chat sessions into *Today*, *Previous 7 Days*, and *Older*.
* **Real-time Search**: Sidebar input filter searches conversation titles on every keystroke.
* **Inline Renaming & Deletion**: Rename conversation titles via modal or delete them with full database cascading.

### 7. 🛠️ Built-in Developer Tools
* **Prompt Library**: Pre-built enterprise templates for CRUD scaffolding, Eloquent optimization, OWASP security audits, Pest test generation, and Redis queue architecture.
* **Artisan Cheatsheet**: Searchable directory of Artisan commands with a 1-click "Ask AI" button.
* **Code Reviewer**: One-click template to review and audit PHP snippets.

### 8. 🛡️ Enterprise Quota & Rate Limiting (`QuotaTracker`)
* **Daily Quota**: Tracks daily requests (default 1,500 requests/day).
* **RPM Limiter**: Restricts traffic to 30 requests per minute to prevent API abuse.
* **Telemetry Indicators**: Progress bar and pill in the top header and sidebar dynamically show remaining quotas in real-time.

### 9. ⚙️ Administrative Dashboard & Web Installer
* **Web GUI Installer**: Multi-step wizard (`/installer/step1`, `/installer/step2`, `/installer/finish`) for zero-code deployments.
* **Admin Settings Panel**: Accessible at `/admin/settings` by Admin users to update AI providers, API keys, default models, and system prompts. Includes a live **Test API Connection** tool.

---

## 🏗️ System Architecture & Data Flow

```text
┌────────────────┐        POST /chat/stream        ┌───────────────────────┐
│  Browser / UI  │ ──────────────────────────────> │  ChatController.php   │
│                │                                 │ (Auth & Quota Check)  │
│  - SSE Reader  │ <────────────────────────────── │                       │
│  - Persona UI  │     data: {"chunk":"..."}       └──────────┬────────────┘
│  - Stop Button │                                            │
└────────────────┘                                            ▼
                                                   ┌───────────────────────┐
                                                   │    AiService.php      │
                                                   │ (Groq/OpenAI/Gemini)  │
                                                   └──────────┬────────────┘
                                                              │
                                        ┌─────────────────────┼─────────────────────┐
                                        ▼                     ▼                     ▼
                                  ┌───────────┐         ┌───────────┐         ┌───────────┐
                                  │ Groq LPU  │         │  OpenAI   │         │  Gemini   │
                                  └───────────┘         └───────────┘         └───────────┘
```

---

## 💻 Tech Stack

* **Backend**: Laravel 11.x, PHP 8.2+ / 8.3 / 8.4
* **Database**: SQLite (default), MySQL, PostgreSQL
* **Frontend**: Vanilla JavaScript (ES6+), Modern Custom CSS (Dark/Light mode design system)
* **Asset Bundler**: Vite 8.x
* **Icons & Rendering**: Lucide Icons, Marked.js (Markdown), Highlight.js (Code Highlighting)
* **Testing Framework**: Pest PHP v3

---

## 🚀 Quick Start Guide (Local Setup)

### 1. Clone the repository
```bash
git clone https://github.com/mahmudpial/mahmudpial-Pial_Laravel_AI_Assistant.git
cd mahmudpial-Pial_Laravel_AI_Assistant
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Setup Environment File
```bash
cp .env.example .env
php artisan key:generate
```

Configure your AI API keys in `.env`:
```env
AI_PROVIDER=groq
GROQ_API_KEY=your_groq_api_key_here
GROQ_MODEL=openai/gpt-oss-120b

# Optional additional providers:
OPENAI_API_KEY=your_openai_api_key_here
DEEPSEEK_API_KEY=your_deepseek_api_key_here
GEMINI_API_KEY=your_gemini_api_key_here
```

### 4. Database Setup & Seeding
```bash
# Run migrations and seed demo accounts
php artisan migrate --seed
```

### 5. Build Frontend Assets
```bash
npm run build
```

### 6. Start Development Server
```bash
php artisan serve
```
Visit `http://127.0.0.1:8000` and log in with `admin@example.com` / `password`.

---

## 🐳 Docker & Cloud Deployment (Render, VPS, Coolify)

The project includes an optimized production `Dockerfile` and `entrypoint.sh` with Nginx, PHP 8.4-FPM, and Supervisor.

### Deploying to Render
1. Push your repository to GitHub.
2. In [Render](https://dashboard.render.com), create a new **Web Service** connected to your repository.
3. Render automatically detects `render.yaml` or you can configure:
   * **Environment**: `Docker`
   * **Health Check Path**: `/up`
4. Add environment variables:
   * `APP_KEY`: Generated via `php artisan key:generate --show`
   * `GROQ_API_KEY` (or your preferred AI key)
   * `APP_URL`: Your Render public URL (e.g. `https://your-app.onrender.com`)

---

## 🧪 Automated Testing

The application features 100% passing tests authoring with **Pest PHP**:

```bash
php artisan test
```

### Test Coverage Highlights:
* Authentication and guest redirection
* Web installer execution
* Conversation creation, renaming, and deletion
* AI Persona parameter validation
* Provider connectivity

---

## 📄 License

This software is released under the [MIT License](LICENSE).

---

## 👨‍💻 Author

**Pial Mahmud**  
*Full Stack Laravel & AI Engineer*  
GitHub: [@mahmudpial](https://github.com/mahmudpial)
