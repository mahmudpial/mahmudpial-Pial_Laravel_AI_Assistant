<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pial Mahmud - AI Chatbot — Laravel + Gemini API</title>

    {{-- Bootstrap 5 CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* ── Chat window ── */
        #chat-window {
            height: calc(100vh - 220px);
            min-height: 300px;
            overflow-y: auto;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 12px;
            scroll-behavior: smooth;
        }

        /* ── Message bubbles ── */
        .msg-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
        }
        .msg-row.user  { flex-direction: row-reverse; }
        .msg-row.model { flex-direction: row; }

        .bubble {
            max-width: 72%;
            padding: .65rem 1rem;
            border-radius: 18px;
            line-height: 1.6;
            font-size: .94rem;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .msg-row.user  .bubble {
            background-color: #0d6efd;
            color: #fff;
            border-bottom-right-radius: 4px;
        }
        .msg-row.model .bubble {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            color: #212529;
            border-bottom-left-radius: 4px;
        }

        /* Avatar circles */
        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            font-weight: 600;
            flex-shrink: 0;
        }
        .avatar-user  { background-color: #0d6efd; color: #fff; }
        .avatar-model { background-color: #198754; color: #fff; }

        /* Timestamp */
        .msg-time {
            font-size: .72rem;
            color: #adb5bd;
            padding: 0 4px;
            align-self: flex-end;
        }

        /* Typing indicator */
        #typing-indicator { display: none; }
        .typing-dots span {
            display: inline-block;
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #adb5bd;
            margin: 0 2px;
            animation: bounce 1.2s infinite;
        }
        .typing-dots span:nth-child(2) { animation-delay: .2s; }
        .typing-dots span:nth-child(3) { animation-delay: .4s; }

        @keyframes bounce {
            0%, 80%, 100% { transform: translateY(0); }
            40%            { transform: translateY(-8px); }
        }

        /* Input area */
        #message-input {
            resize: none;
            border-radius: 12px;
            padding: .6rem .9rem;
            font-size: .94rem;
            border: 1px solid #ced4da;
            transition: border-color .2s;
        }
        #message-input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, .15);
            outline: none;
        }

        /* Send button */
        #send-btn {
            width: 44px; height: 44px;
            border-radius: 50%;
            padding: 0;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        /* Code blocks inside bot replies */
        .bubble code {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 2px 5px;
            font-size: .85em;
            color: #c7254e;
        }
        .bubble pre {
            background: #1e1e1e;
            color: #d4d4d4;
            border-radius: 8px;
            padding: .75rem 1rem;
            overflow-x: auto;
            font-size: .82rem;
        }
        .bubble pre code {
            background: none;
            border: none;
            color: inherit;
            padding: 0;
        }
    </style>
</head>
<body>

<div class="container-md py-4" style="max-width: 780px;">

    {{-- Header --}}
    <div class="card shadow-sm border-0 mb-0" style="border-radius: 16px 16px 0 0;">
        <div class="card-header border-0 d-flex align-items-center justify-content-between px-4 py-3"
             style="background: linear-gradient(135deg, #0d6efd, #0a58ca); border-radius: 16px 16px 0 0;">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar avatar-model" style="width:42px;height:42px;font-size:1rem;">
                    <i class="bi bi-robot"></i>
                </div>
                <div>
                    <h6 class="mb-0 text-white fw-semibold">Pial Mahmud's Laravel AI Assistant</h6>
                    <small class="text-white opacity-75">Powered by Gemini API</small>
                </div>
            </div>
            <button id="clear-btn" class="btn btn-sm btn-outline-light" title="Clear chat">
                <i class="bi bi-trash3"></i> Clear
            </button>
        </div>
    </div>

    {{-- Chat window --}}
    <div class="card shadow-sm border-0" style="border-radius: 0; border-top: 1px solid #e9ecef;">
        <div id="chat-window">

            {{-- Welcome message (static, always shown) --}}
            <div class="msg-row model">
                <div class="avatar avatar-model"><i class="bi bi-robot"></i></div>
                <div class="bubble">
                    👋 আমি তোমার Laravel AI assistant। যেকোনো Laravel, PHP বা প্রোগ্রামিং প্রশ্ন করো!
                </div>
            </div>

            {{-- Render session history on page load --}}
            @foreach ($history as $turn)
                @if ($turn['role'] === 'user')
                    <div class="msg-row user">
                        <span class="msg-time">{{ now()->format('H:i') }}</span>
                        <div class="bubble">{{ $turn['parts'][0]['text'] }}</div>
                        <div class="avatar avatar-user">তুমি</div>
                    </div>
                @else
                    <div class="msg-row model">
                        <div class="avatar avatar-model"><i class="bi bi-robot"></i></div>
                        <div class="bubble">{!! nl2br(e($turn['parts'][0]['text'])) !!}</div>
                        <span class="msg-time">{{ now()->format('H:i') }}</span>
                    </div>
                @endif
            @endforeach

            {{-- Typing indicator --}}
            <div class="msg-row model" id="typing-indicator">
                <div class="avatar avatar-model"><i class="bi bi-robot"></i></div>
                <div class="bubble typing-dots">
                    <span></span><span></span><span></span>
                </div>
            </div>

        </div>
    </div>

    {{-- Input area --}}
    <div class="card shadow-sm border-0" style="border-radius: 0 0 16px 16px; border-top: 1px solid #e9ecef;">
        <div class="card-body px-4 py-3">
            <div class="d-flex align-items-end gap-2">
                <textarea
                    id="message-input"
                    class="form-control flex-grow-1"
                    rows="1"
                    placeholder="write your prompt"
                ></textarea>
                <button id="send-btn" class="btn btn-primary" title="Send">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i>
                    Session-এ সর্বোচ্চ ২০টি turn সংরক্ষিত হয়।
                </small>
                <small id="char-count" class="text-muted">0 / 2000</small>
            </div>
        </div>
    </div>

    <div class="text-center mt-3">
        <small class="text-muted px-3 py-2 d-inline-block rounded-pill" style="background: rgba(13, 110, 253, 0.06); border: 1px solid rgba(13, 110, 253, 0.12);">
            Owned by Pial Mahmud
        </small>
    </div>

</div>{{-- /container --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const chatWindow    = document.getElementById('chat-window');
    const messageInput  = document.getElementById('message-input');
    const sendBtn       = document.getElementById('send-btn');
    const clearBtn      = document.getElementById('clear-btn');
    const typingIndicator = document.getElementById('typing-indicator');
    const charCount     = document.getElementById('char-count');

    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // ── Helpers ────────────────────────────────────────────────────────

    function scrollBottom() {
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    function now() {
        return new Date().toLocaleTimeString('bn-BD', { hour: '2-digit', minute: '2-digit' });
    }

    function appendMessage(role, text) {
        const row = document.createElement('div');
        row.className = `msg-row ${role}`;

        const timeSpan = `<span class="msg-time">${now()}</span>`;

        if (role === 'user') {
            row.innerHTML = `
                ${timeSpan}
                <div class="bubble">${escapeHtml(text)}</div>
                <div class="avatar avatar-user">তুমি</div>`;
        } else {
            // nl2br + basic code-fence rendering for model replies
            row.innerHTML = `
                <div class="avatar avatar-model"><i class="bi bi-robot"></i></div>
                <div class="bubble">${formatReply(text)}</div>
                ${timeSpan}`;
        }

        // Insert BEFORE typing indicator
        chatWindow.insertBefore(row, typingIndicator);
        scrollBottom();
    }

    function escapeHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /**
     * Minimal markdown-ish formatting for bot replies:
     *  - ```code blocks```
     *  - `inline code`
     *  - newlines → <br>
     */
    function formatReply(text) {
        // 1. Escape HTML first
        let safe = escapeHtml(text);

        // 2. Fenced code blocks  ```lang\n...\n```
        safe = safe.replace(/```(\w*)\n([\s\S]*?)```/g, (_, lang, code) => {
            return `<pre><code>${code.trimEnd()}</code></pre>`;
        });

        // 3. Inline `code`
        safe = safe.replace(/`([^`]+)`/g, '<code>$1</code>');

        // 4. Newlines outside pre blocks
        safe = safe.replace(/(?!<\/?(pre|code)[^>]*>)\n/g, '<br>');

        return safe;
    }

    function setLoading(loading) {
        typingIndicator.style.display = loading ? 'flex' : 'none';
        sendBtn.disabled = loading;
        messageInput.disabled = loading;
        if (loading) scrollBottom();
    }

    // ── Auto-resize textarea ───────────────────────────────────────────

    messageInput.addEventListener('input', () => {
        messageInput.style.height = 'auto';
        messageInput.style.height = Math.min(messageInput.scrollHeight, 140) + 'px';
        charCount.textContent = `${messageInput.value.length} / 2000`;
    });

    // ── Send on Enter (Shift+Enter = newline) ─────────────────────────

    messageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    sendBtn.addEventListener('click', sendMessage);

    // ── Main send function ─────────────────────────────────────────────

    async function sendMessage() {
        const text = messageInput.value.trim();
        if (!text || sendBtn.disabled) return;

        // Append user bubble
        appendMessage('user', text);

        // Reset input
        messageInput.value = '';
        messageInput.style.height = 'auto';
        charCount.textContent = '0 / 2000';

        setLoading(true);

        try {
            const res = await fetch('{{ route("chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message: text }),
            });

            const data = await res.json();

            if (data.success) {
                appendMessage('model', data.reply);
            } else {
                appendMessage('model', '⚠️ কোনো সমস্যা হয়েছে। আবার চেষ্টা করো।');
            }

        } catch (err) {
            appendMessage('model', '⚠️ নেটওয়ার্ক সমস্যা। সংযোগ চেক করো।');
            console.error(err);
        } finally {
            setLoading(false);
            messageInput.focus();
        }
    }

    // ── Clear chat ─────────────────────────────────────────────────────

    clearBtn.addEventListener('click', async () => {
        if (!confirm('সব conversation মুছে ফেলবো?')) return;

        await fetch('{{ route("chat.clear") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
        });

        // Remove all dynamic bubbles (keep welcome message)
        const rows = chatWindow.querySelectorAll('.msg-row:not(#typing-indicator)');
        rows.forEach((row, i) => { if (i > 0) row.remove(); });
    });

    // ── Init: scroll to bottom if history exists ───────────────────────
    scrollBottom();
    messageInput.focus();
</script>

</body>
</html>
