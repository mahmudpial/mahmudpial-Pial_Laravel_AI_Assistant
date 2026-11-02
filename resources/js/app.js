document.addEventListener('DOMContentLoaded', () => {
    // DOM Elements
    const sendBtn = document.getElementById('send-btn');
    const inputField = document.getElementById('message-input');
    const chatContainer = document.getElementById('chat-container');
    const chatWindow = document.getElementById('chat-window');
    const sidebar = document.getElementById('sidebar');
    const collapseBtn = document.getElementById('collapse-sidebar');
    const drawerBackdrop = document.getElementById('drawer-backdrop');
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const newChatBtn = document.getElementById('new-chat-btn');
    const clearChatHeaderBtn = document.getElementById('clear-chat-header-btn');
    const quotaText = document.getElementById('quota-text');
    const quotaBarFill = document.getElementById('quota-bar-fill');
    const quotaMiniFill = document.getElementById('quota-mini-fill');
    const sidebarQuotaFill = document.getElementById('sidebar-quota-fill');
    const sidebarQuotaNum = document.getElementById('sidebar-quota-num');
    const activeConversationInput = document.getElementById('active-conversation-id');
    const searchInput = document.getElementById('conversation-search');
    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toast-msg');

    let toastTimeout = null;

    // ── 1. Initialize Icons, Themes & History ─────────────────
    refreshIcons();
    initTheme();
    hydrateHistory();

    function refreshIcons() {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    function initTheme() {
        const savedTheme = localStorage.getItem('pial_theme') || 'dark';
        document.body.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);

        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const current = document.body.getAttribute('data-theme') || 'dark';
                const next = current === 'dark' ? 'light' : 'dark';
                document.body.setAttribute('data-theme', next);
                localStorage.setItem('pial_theme', next);
                updateThemeIcon(next);
            });
        }
    }

    function updateThemeIcon(theme) {
        if (!themeIcon) return;
        if (theme === 'dark') {
            themeIcon.setAttribute('data-lucide', 'sun');
        } else {
            themeIcon.setAttribute('data-lucide', 'moon');
        }
        refreshIcons();
    }

    // ── 2. Toast Notification ────────────────────────────────
    window.showToast = function (message) {
        if (!toast || !toastMsg) return;
        toastMsg.textContent = message;
        toast.classList.add('show');
        if (toastTimeout) clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            toast.classList.remove('show');
        }, 2500);
    };

    // ── 3. Sidebar & Mobile Drawer ───────────────────────────
    if (collapseBtn && sidebar) {
        collapseBtn.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('mobile-open');
                if (drawerBackdrop) {
                    drawerBackdrop.classList.toggle('active', sidebar.classList.contains('mobile-open'));
                }
            } else {
                sidebar.classList.toggle('collapsed');
            }
        });
    }

    if (drawerBackdrop) {
        drawerBackdrop.addEventListener('click', () => {
            if (sidebar) sidebar.classList.remove('mobile-open');
            drawerBackdrop.classList.remove('active');
        });
    }

    // ── 4. Conversation Search Filter ────────────────────────
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            const items = document.querySelectorAll('.conversation-item');
            items.forEach((item) => {
                const title = item.getAttribute('data-title') || '';
                if (title.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });

            // Hide/show group titles if empty
            document.querySelectorAll('.history-group').forEach((group) => {
                const visibleChildren = group.querySelectorAll('.conversation-item[style*="display: flex"], .conversation-item:not([style*="display: none"])');
                const hasVisible = Array.from(visibleChildren).some(el => el.style.display !== 'none');
                group.style.display = hasVisible ? 'block' : 'none';
            });
        });
    }

    // ── 5. Auto-Resizing Textarea & Keydown Events ───────────
    if (inputField) {
        inputField.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 200) + 'px';
        });

        inputField.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (sendBtn && !sendBtn.disabled) {
                    sendBtn.click();
                }
            }
        });
    }

    // Quick prompt runner accessible globally
    window.sendPrompt = function (text) {
        if (!inputField || !sendBtn) return;
        inputField.value = text;
        inputField.style.height = 'auto';
        inputField.style.height = Math.min(inputField.scrollHeight, 200) + 'px';
        sendBtn.click();
    };

    // ── 6. New Chat / Start Fresh ────────────────────────────
    if (newChatBtn) {
        newChatBtn.addEventListener('click', () => {
            window.location.href = '/chat';
        });
    }

    if (clearChatHeaderBtn) {
        clearChatHeaderBtn.addEventListener('click', () => {
            if (confirm('Start a new chat session?')) {
                window.location.href = '/chat';
            }
        });
    }

    // ── 7. Message Sending & AI Response ─────────────────────
    if (sendBtn && inputField && chatContainer) {
        sendBtn.addEventListener('click', async () => {
            const text = inputField.value.trim();
            if (!text) return;

            // Remove empty state hero if present
            const emptyState = document.getElementById('empty-state');
            if (emptyState) emptyState.remove();

            // Append User message
            appendUserMessage(text);
            inputField.value = '';
            inputField.style.height = 'auto';

            // Show Typing Indicator
            const typingId = showTypingIndicator();
            setSendingState(true);

            try {
                const convId = activeConversationInput ? activeConversationInput.value : null;

                const response = await fetch('/chat/stream', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'text/event-stream',
                    },
                    body: JSON.stringify({
                        message: text,
                        conversation_id: convId ? parseInt(convId) : null,
                    }),
                });

                removeElement(typingId);

                // If server returned error JSON (e.g. 429 quota)
                if (!response.ok && response.headers.get('content-type')?.includes('application/json')) {
                    const errJson = await response.json();
                    appendAIMessage(errJson.error || 'Server error occurred.');
                    setSendingState(false);
                    return;
                }

                // Create streaming AI message container
                const msgDiv = document.createElement('div');
                msgDiv.className = 'message ai';
                msgDiv.innerHTML = `
                    <div class="ai-avatar">
                        <i data-lucide="sparkles" style="width: 18px; height: 18px;"></i>
                    </div>
                    <div class="message-content-wrapper">
                        <div class="message-body"></div>
                        <div class="message-actions" style="display: none;">
                            <button class="action-btn copy-msg-btn">
                                <i data-lucide="copy" style="width: 13px; height: 13px;"></i>
                                <span>Copy</span>
                            </button>
                            <span style="font-size: 0.72rem; color: var(--text-muted); margin-left: 6px;">Just now</span>
                        </div>
                    </div>
                `;
                chatContainer.appendChild(msgDiv);
                refreshIcons();

                const messageBody = msgDiv.querySelector('.message-body');
                const messageActions = msgDiv.querySelector('.message-actions');
                let accumulatedText = '';

                // Read SSE stream
                const reader = response.body.getReader();
                const decoder = new TextDecoder('utf-8');
                let buffer = '';

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop(); // Keep incomplete line in buffer

                    for (const line of lines) {
                        const trimmed = line.trim();
                        if (!trimmed || !trimmed.startsWith('data:')) continue;

                        try {
                            const data = JSON.parse(trimmed.substring(5).trim());

                            if (data.type === 'meta') {
                                if (data.conversation_id && (!convId || convId !== String(data.conversation_id))) {
                                    if (activeConversationInput) {
                                        activeConversationInput.value = data.conversation_id;
                                    }
                                    window.history.pushState(null, '', '/chat/' + data.conversation_id);
                                    reloadSidebarConversations();
                                }
                            } else if (data.type === 'chunk') {
                                accumulatedText += data.content;
                                messageBody.innerHTML = parseMarkdown(accumulatedText);
                                scrollToBottom();
                            } else if (data.type === 'done') {
                                if (data.quotaStatus && quotaText) {
                                    quotaText.textContent = data.quotaStatus;
                                }
                                if (typeof data.quota !== 'undefined' && sidebarQuotaNum) {
                                    sidebarQuotaNum.textContent = data.quota;
                                }
                                if (typeof data.quotaPercent !== 'undefined') {
                                    const pct = Math.max(0, Math.min(100, data.quotaPercent)) + '%';
                                    if (quotaBarFill) quotaBarFill.style.width = pct;
                                    if (quotaMiniFill) quotaMiniFill.style.width = pct;
                                    if (sidebarQuotaFill) sidebarQuotaFill.style.width = pct;
                                }
                            }
                        } catch (e) {
                            // Non-json SSE heartbeat, skip
                        }
                    }
                }

                // Finalize decorations once stream completes
                decorateCodeBlocks(msgDiv);
                attachMessageActionEvents(msgDiv, accumulatedText);
                if (messageActions) messageActions.style.display = 'flex';
                refreshIcons();

            } catch (err) {
                console.error(err);
                removeElement(typingId);
                appendAIMessage('⚠️ Stream connection error. Please try again.');
            } finally {
                setSendingState(false);
                scrollToBottom();
            }
        });
    }

    function setSendingState(isSending) {
        if (!sendBtn) return;
        sendBtn.disabled = isSending;
        if (isSending) {
            sendBtn.innerHTML = `<i data-lucide="loader-2" style="width: 18px; height: 18px; animation: spin 1s linear infinite;"></i>`;
        } else {
            sendBtn.innerHTML = `<i data-lucide="arrow-up" style="width: 18px; height: 18px;"></i>`;
        }
        refreshIcons();
    }

    function appendUserMessage(text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'message user';
        msgDiv.innerHTML = `
            <div class="message-content-wrapper">
                <div class="message-body">${escapeHtml(text)}</div>
            </div>
        `;
        chatContainer.appendChild(msgDiv);
        scrollToBottom();
    }

    function appendAIMessage(markdownText) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'message ai';

        const renderedHtml = parseMarkdown(markdownText);

        msgDiv.innerHTML = `
            <div class="ai-avatar">
                <i data-lucide="sparkles" style="width: 18px; height: 18px;"></i>
            </div>
            <div class="message-content-wrapper">
                <div class="message-body">${renderedHtml}</div>
                <div class="message-actions">
                    <button class="action-btn copy-msg-btn">
                        <i data-lucide="copy" style="width: 13px; height: 13px;"></i>
                        <span>Copy</span>
                    </button>
                    <span style="font-size: 0.72rem; color: var(--text-muted); margin-left: 6px;">Just now</span>
                </div>
            </div>
        `;

        chatContainer.appendChild(msgDiv);
        decorateCodeBlocks(msgDiv);
        attachMessageActionEvents(msgDiv, markdownText);
        refreshIcons();
        scrollToBottom();
    }

    function showTypingIndicator() {
        const id = 'typing-' + Date.now();
        const typingDiv = document.createElement('div');
        typingDiv.id = id;
        typingDiv.className = 'message ai';
        typingDiv.innerHTML = `
            <div class="ai-avatar">
                <i data-lucide="sparkles" style="width: 18px; height: 18px;"></i>
            </div>
            <div class="message-content-wrapper">
                <div class="typing-box">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        `;
        chatContainer.appendChild(typingDiv);
        refreshIcons();
        scrollToBottom();
        return id;
    }

    function removeElement(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function scrollToBottom() {
        if (chatWindow) {
            chatWindow.scrollTop = chatWindow.scrollHeight;
        }
    }

    // ── 8. Markdown & Syntax Highlighting ───────────────────
    function parseMarkdown(text) {
        if (window.marked) {
            return window.marked.parse(text);
        }
        return escapeHtml(text).replace(/\n/g, '<br>');
    }

    function decorateCodeBlocks(container) {
        const preElements = container.querySelectorAll('pre');
        preElements.forEach((pre) => {
            const codeEl = pre.querySelector('code');
            if (!codeEl) return;

            let lang = 'code';
            codeEl.classList.forEach((cls) => {
                if (cls.startsWith('language-')) {
                    lang = cls.replace('language-', '');
                }
            });

            if (window.hljs) {
                window.hljs.highlightElement(codeEl);
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'code-wrapper';

            const header = document.createElement('div');
            header.className = 'code-header';
            header.innerHTML = `
                <span class="code-lang">
                    <i data-lucide="file-code" style="width: 14px; height: 14px;"></i>
                    ${lang}
                </span>
                <button type="button" class="copy-code-btn">
                    <i data-lucide="copy" style="width: 13px; height: 13px;"></i>
                    <span>Copy</span>
                </button>
            `;

            const copyBtn = header.querySelector('.copy-code-btn');
            copyBtn.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(codeEl.textContent);
                    copyBtn.innerHTML = `<i data-lucide="check" style="width: 13px; height: 13px; color: #10b981;"></i><span>Copied!</span>`;
                    window.showToast('Code copied to clipboard');
                    refreshIcons();
                    setTimeout(() => {
                        copyBtn.innerHTML = `<i data-lucide="copy" style="width: 13px; height: 13px;"></i><span>Copy</span>`;
                        refreshIcons();
                    }, 2000);
                } catch (e) {
                    window.showToast('Failed to copy code');
                }
            });

            pre.parentNode.insertBefore(wrapper, pre);
            wrapper.appendChild(header);
            wrapper.appendChild(pre);
        });
    }

    function attachMessageActionEvents(msgContainer, rawText) {
        const copyBtn = msgContainer.querySelector('.copy-msg-btn');
        if (copyBtn) {
            copyBtn.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(rawText);
                    window.showToast('Response copied to clipboard');
                } catch (e) {
                    window.showToast('Failed to copy response');
                }
            });
        }
    }

    function hydrateHistory() {
        const rawMarkdowns = document.querySelectorAll('.raw-markdown');
        rawMarkdowns.forEach((el) => {
            const encoded = el.getAttribute('data-content');
            if (encoded) {
                try {
                    const text = atob(encoded);
                    el.innerHTML = parseMarkdown(text);
                    decorateCodeBlocks(el);
                    const parentMsg = el.closest('.message.ai');
                    if (parentMsg) {
                        attachMessageActionEvents(parentMsg, text);
                    }
                } catch (e) {
                    console.error('Error decoding markdown history:', e);
                }
            }
        });
        refreshIcons();
    }

    // ── 9. Modals & Presets System ───────────────────────────
    window.openModal = function (id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('active');
            refreshIcons();
        }
    };

    window.closeModal = function (id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('active');
        }
    };

    // Close modal on escape key or clicking backdrop
    document.querySelectorAll('.modal-overlay').forEach((overlay) => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach((m) => m.classList.remove('active'));
        }
    });

    // Trigger buttons
    const openPromptLibBtn = document.getElementById('open-prompt-library-btn');
    if (openPromptLibBtn) {
        openPromptLibBtn.addEventListener('click', () => window.openModal('modal-prompt-library'));
    }

    const openCheatsheetBtn = document.getElementById('open-cheatsheet-btn');
    if (openCheatsheetBtn) {
        openCheatsheetBtn.addEventListener('click', () => window.openModal('modal-cheatsheet'));
    }

    const openSettingsBtn = document.getElementById('open-settings-btn');
    if (openSettingsBtn) {
        openSettingsBtn.addEventListener('click', () => window.openModal('modal-settings'));
    }

    const openCodeReviewerBtn = document.getElementById('open-code-reviewer-btn');
    if (openCodeReviewerBtn) {
        openCodeReviewerBtn.addEventListener('click', () => {
            if (inputField) {
                inputField.value = `Please review and audit the following PHP/Laravel code for bugs, performance bottlenecks, and security issues:\n\n\`\`\`php\n// Paste your code here\n\`\`\``;
                inputField.focus();
                inputField.style.height = 'auto';
                inputField.style.height = Math.min(inputField.scrollHeight, 200) + 'px';
                window.showToast('Code Review template loaded into prompt');
            }
        });
    }

    // Use prompt from modal
    window.usePromptTemplate = function (templateText) {
        window.closeModal('modal-prompt-library');
        if (inputField) {
            inputField.value = templateText;
            inputField.focus();
            inputField.style.height = 'auto';
            inputField.style.height = Math.min(inputField.scrollHeight, 200) + 'px';
        }
    };

    // Use command from cheatsheet
    window.askCheatsheet = function (command) {
        window.closeModal('modal-cheatsheet');
        window.sendPrompt(`Explain how to use this Laravel command, its options, and real-world examples: \`${command}\``);
    };

    // Cheatsheet Search Filter
    const cheatsheetSearch = document.getElementById('cheatsheet-search-input');
    if (cheatsheetSearch) {
        cheatsheetSearch.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            document.querySelectorAll('.cheatsheet-item').forEach((item) => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(query) ? 'flex' : 'none';
            });
        });
    }

    // ── 10. Conversation Management (Rename & Delete) ────────
    window.openRenameModal = function (id, currentTitle) {
        const modal = document.getElementById('modal-rename');
        const idInput = document.getElementById('rename-conversation-id');
        const titleInput = document.getElementById('rename-title-input');
        if (modal && idInput && titleInput) {
            idInput.value = id;
            titleInput.value = currentTitle;
            window.openModal('modal-rename');
            setTimeout(() => titleInput.focus(), 100);
        }
    };

    const saveRenameBtn = document.getElementById('save-rename-btn');
    if (saveRenameBtn) {
        saveRenameBtn.addEventListener('click', async () => {
            const idInput = document.getElementById('rename-conversation-id');
            const titleInput = document.getElementById('rename-title-input');
            const newTitle = titleInput ? titleInput.value.trim() : '';

            if (!idInput || !newTitle) return;

            try {
                const res = await fetch(`/chat/${idInput.value}/rename`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                    body: JSON.stringify({ title: newTitle }),
                });

                if (res.ok) {
                    window.closeModal('modal-rename');
                    window.showToast('Chat renamed successfully');
                    reloadSidebarConversations();
                }
            } catch (e) {
                window.showToast('Failed to rename chat');
            }
        });
    }

    window.deleteConversation = async function (id) {
        if (!confirm('Are you sure you want to delete this chat conversation?')) return;

        try {
            const res = await fetch(`/chat/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });

            if (res.ok) {
                window.showToast('Chat deleted');
                const activeId = activeConversationInput ? activeConversationInput.value : null;
                if (activeId && String(activeId) === String(id)) {
                    window.location.href = '/chat';
                } else {
                    reloadSidebarConversations();
                }
            }
        } catch (e) {
            window.showToast('Failed to delete chat');
        }
    };

    // Reload sidebar conversations dynamically via API
    async function reloadSidebarConversations() {
        try {
            const res = await fetch('/conversations');
            if (!res.ok) return;
            const data = await res.json();
            const container = document.getElementById('history-container');
            if (!container) return;

            const activeId = activeConversationInput ? activeConversationInput.value : null;
            let html = '';

            const renderGroup = (title, list) => {
                if (!list || list.length === 0) return '';
                let g = `<div class="history-group"><div class="history-group-title">${title}</div>`;
                list.forEach((c) => {
                    const isActive = activeId && String(activeId) === String(c.id) ? 'active' : '';
                    const safeTitle = escapeHtml(c.title);
                    g += `
                        <a href="/chat/${c.id}" class="conversation-item ${isActive}" data-id="${c.id}" data-title="${safeTitle.toLowerCase()}">
                            <i data-lucide="message-square" style="width: 15px; height: 15px; flex-shrink: 0;"></i>
                            <span class="conversation-title-text">${safeTitle}</span>
                            <div class="conversation-item-actions">
                                <button type="button" class="item-action-btn rename-btn" title="Rename" onclick="event.preventDefault(); window.openRenameModal(${c.id}, '${escapeJsString(c.title)}')">
                                    <i data-lucide="pencil" style="width: 12px; height: 12px;"></i>
                                </button>
                                <button type="button" class="item-action-btn delete delete-btn" title="Delete" onclick="event.preventDefault(); window.deleteConversation(${c.id})">
                                    <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                                </button>
                            </div>
                        </a>
                    `;
                });
                g += `</div>`;
                return g;
            };

            html += renderGroup('Today', data.today);
            html += renderGroup('Previous 7 Days', data.previous7Days);
            html += renderGroup('Older', data.older);

            container.innerHTML = html;
            refreshIcons();
        } catch (e) {
            console.error('Failed to reload sidebar:', e);
        }
    }

    // ── Helper Utilities ─────────────────────────────────────
    function getCsrfToken() {
        return (
            document.querySelector('meta[name="csrf-token"]')?.content ||
            document.cookie
                .split('; ')
                .find((row) => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1] ||
            ''
        );
    }

    function escapeHtml(string) {
        const entityMap = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        };
        return String(string).replace(/[&<>"']/g, (s) => entityMap[s]);
    }

    function escapeJsString(str) {
        return (str || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    }
});
