# Pial Mahmud Laravel AI Assistant

A professional Laravel-based AI chatbot application powered by the Google Gemini API.
The app provides a clean chat experience, session-based conversation history, and a simple interface for asking Laravel, PHP, and general programming questions.

## Project Overview

This project is a custom Laravel application developed by Pial Mahmud.
It uses Laravel as the backend framework and Gemini as the AI engine for generating responses.

The application is designed to be:

- Simple to use
- Easy to maintain
- Fast to set up locally
- Ready for future feature expansion

## Live Purpose

The chatbot is intended to help users:

- Ask Laravel questions
- Get PHP development guidance
- Explore programming concepts
- Interact with an AI assistant in a lightweight web UI

## Features

- AI chat powered by Google Gemini API
- Session-based chat history
- Clear chat action
- Clean Bootstrap 5 UI
- Responsive layout
- Message validation and input limit
- 20-turn conversation memory window
- Role-based message rendering for user and assistant
- Simple architecture for easy extension

## Architecture

The application follows a straightforward Laravel service-based structure:

1. `routes/web.php` defines the chat routes.
2. `ChatController` handles page loading, message sending, and chat clearing.
3. `GeminiService` prepares conversation history and sends requests to Gemini.
4. Session storage keeps the current chat history for each user session.
5. `resources/views/chat.blade.php` renders the UI and chat bubbles.

### Request Flow

```text
User message -> Route -> ChatController -> GeminiService -> Gemini API
           <- JSON response <- Controller <- Service <- AI reply
```

## Project Structure

```text
app/
  Http/Controllers/ChatController.php
  Services/GeminiService.php
config/
  services.php
database/
  migrations/
  seeders/
public/
resources/
  views/chat.blade.php
routes/
  web.php
storage/
tests/
vendor/
```

### Important Files

- [routes/web.php](/Applications/xampp/xamppfiles/htdocs/Pial_Laravel_AI_Assistant/routes/web.php) - Application routes
- [app/Http/Controllers/ChatController.php](/Applications/xampp/xamppfiles/htdocs/Pial_Laravel_AI_Assistant/app/Http/Controllers/ChatController.php) - Chat request handling
- [app/Services/GeminiService.php](/Applications/xampp/xamppfiles/htdocs/Pial_Laravel_AI_Assistant/app/Services/GeminiService.php) - Gemini API integration
- [resources/views/chat.blade.php](/Applications/xampp/xamppfiles/htdocs/Pial_Laravel_AI_Assistant/resources/views/chat.blade.php) - Main chatbot UI
- [config/services.php](/Applications/xampp/xamppfiles/htdocs/Pial_Laravel_AI_Assistant/config/services.php) - API credential configuration

## Requirements

- PHP 8.3 or higher
- Composer
- Node.js and npm
- MySQL, SQLite, or another Laravel-supported database
- A valid Gemini API key

## Installation

### 1. Clone or download the project

Place the project in your local development environment.

### 2. Install PHP dependencies

```bash
composer install
```

This creates the `vendor/` directory and installs all required PHP packages.

### 3. Install frontend dependencies

```bash
npm install
```

### 4. Configure environment variables

Copy the example environment file if needed:

```bash
cp .env.example .env
```

Set the following values in `.env`:

```env
APP_NAME="Pial Mahmud Laravel AI Assistant"
GEMINI_API_KEY=your_gemini_api_key
GEMINI_MODEL=gemini-2.5-flash
```

If you are using SQLite, make sure the database file exists and the database path is correct.

### 5. Generate the application key

```bash
php artisan key:generate
```

### 6. Run database migrations

```bash
php artisan migrate
```

### 7. Build frontend assets

```bash
npm run build
```

For local development with hot reload:

```bash
npm run dev
```

### 8. Start the application

```bash
php artisan serve
```

Then open the app in your browser.

## Usage

- Open the homepage
- Type a question in the chat box
- Press send
- The assistant replies using Gemini
- Use the clear button to remove the current session conversation

## Configuration

The Gemini credentials are loaded from [config/services.php](/Applications/xampp/xamppfiles/htdocs/Pial_Laravel_AI_Assistant/config/services.php).

Environment keys used by the app:

- `GEMINI_API_KEY`
- `GEMINI_MODEL`

## Development Notes

- Chat history is stored in session, not permanently in the database.
- The application keeps only the latest 20 turns to control memory usage.
- The assistant prompt is defined in `ChatController`.
- The UI is built with Bootstrap 5 and Bootstrap Icons.

## Testing

Run the test suite with:

```bash
php artisan test
```

## License

This project is proprietary and all rights are reserved by the owner.

Third-party dependencies used in this repository keep their own original licenses.

## Footer

Built with Laravel, Bootstrap, and Google Gemini API.
Owned and maintained by Pial Mahmud.
