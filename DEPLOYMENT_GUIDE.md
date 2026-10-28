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
| `APP_URL` | `"https://your-app.onrender.com"` | Your Render URL |

### Database Settings (SQLite)
| Key | Value | Note |
| :--- | :--- | :--- |
| `DB_CONNECTION` | `"sqlite"` | Use SQLite driver |
| `DB_DATABASE` | `"/var/lib/data/database.sqlite"` | Path on the persistent disk |

### AI Service Settings
| Key | Value | Note |
| :--- | :--- | :--- |
| `GEMINI_API_KEY` | `"your_api_key_here"` | From Google AI Studio |

### Cache & Session Settings
| Key | Value | Note |
| :--- | :--- | :--- |
| `CACHE_DRIVER` | `"file"` | File-based cache |
| `SESSION_DRIVER` | `"file"` | File-based sessions |
| `QUEUE_CONNECTION` | `"sync"` | Synchronous queue |

---

## 3. Persistent Disk Setup (CRITICAL)
Because Render's file system is ephemeral, you **must** add a disk to prevent your database from being deleted on every restart.

1. Go to the **Disks** tab in your Render service.
2. Click **Add Disk**.
3. **Name:** `laravel-data`
4. **Mount Path:** `/var/lib/data`
5. **Size:** `1GB`

---

## 4. Post-Deployment Step
After the site is successfully deployed, you must run the migrations to create the database tables:

1. Go to the **Shell** tab in your Render dashboard.
2. Run the following command:
   ```bash
   php artisan migrate --force
   ```

---
**Prepared by Codex**
