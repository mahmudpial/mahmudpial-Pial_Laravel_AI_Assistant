# 🚀 Render Deployment Guide for Pial AI Assistant

This document contains all the necessary configurations to deploy this Laravel application to Render using Docker and SQLite.

## 1. Render Service Configuration
- **Runtime:** `Docker`
- **Build Command:** (Handled by Dockerfile)
- **Start Command:** (Handled by Dockerfile)

---

## 2. Environment Variables (Env Vars)
Add these keys and values in the **Environment** tab of your Render service:

### Core App Settings
| Key | Value | Note |
| :--- | :--- | :--- |
| `APP_NAME` | `"Pial AI Assistant"` | Application Name |
| `APP_ENV` | `"production"` | Production environment |
| `APP_KEY` | `base64:your_key_here` | Copy from your local `.env` |
| `APP_DEBUG` | `"false"` | Disable debug for security |
| `APP_URL` | `"https://your-app.onrender.com"` | Your actual Render HTTPS URL |

### Database Settings (SQLite)
| Key | Value | Note |
| :--- | :--- | :--- |
| `DB_CONNECTION` | `"sqlite"` | Use SQLite driver |
| `DB_DATABASE` | `"/var/lib/data/database.sqlite"` | Path on the persistent disk (or leave blank to use default) |

### AI Service Settings (Select your provider)
| Key | Value | Note |
| :--- | :--- | :--- |
| `AI_PROVIDER` | `"groq"` | Default provider (`groq`, `openai`, `gemini`, `deepseek`) |
| `GROQ_API_KEY` | `"gsk_..."` | Groq LPU API Key |
| `GROQ_MODEL` | `"openai/gpt-oss-120b"` | Active Groq model identifier |
| `OPENAI_API_KEY` | `"sk-..."` | (Optional) OpenAI API Key |
| `DEEPSEEK_API_KEY`| `"sk-..."` | (Optional) DeepSeek API Key |
| `GEMINI_API_KEY` | `"AIza..."` | (Optional) Google Gemini API Key |

### Cache & Session Settings
| Key | Value | Note |
| :--- | :--- | :--- |
| `CACHE_DRIVER` | `"file"` | File-based cache |
| `SESSION_DRIVER` | `"file"` | File-based sessions |
| `QUEUE_CONNECTION` | `"sync"` | Synchronous queue |

---

## 3. Persistent Disk Setup (Recommended for SQLite)
Because Render's container file system is ephemeral on restarts:
1. Go to the **Disks** tab in your Render service.
2. Click **Add Disk**.
3. **Name:** `laravel-data`
4. **Mount Path:** `/var/lib/data`
5. **Size:** `1GB`

---

## 4. Automated Startup & Migrations
The container `entrypoint.sh` script automatically:
- Purges any stale local Vite dev markers (`public/hot`) so production CSS/JS always load cleanly over HTTPS.
- Automatically initializes the SQLite database file and directory with proper `www-data` permissions.
- Automatically executes `php artisan migrate --force` on boot.
- Automatically seeds the initial admin account (`admin@example.com` / `password`).
- Dynamically adapts to Render's assigned `$PORT`.

