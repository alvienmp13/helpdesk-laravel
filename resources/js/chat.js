export function initChat({ data, showDialog, showToast }) {
    const root = document.querySelector('.chat-page');
    if (!root) return null;

    // All sessions, drafts, feedback, and replies live only in this page's memory.
    const sessions = structuredClone(data.sessions).map((session) => ({ ...session, draft: '', pending: false, scroll: 0 }));
    const history = root.querySelector('[data-chat-history]');
    const historySearch = root.querySelector('[data-chat-search]');
    const historyBody = root.querySelector('#chat-history-body');
    const historyToggle = root.querySelector('[data-history-toggle]');
    const feed = root.querySelector('#chat-message-feed');
    const messages = root.querySelector('[data-chat-messages]');
    const input = root.querySelector('[data-chat-input]');
    const submit = root.querySelector('[data-chat-send]');
    const error = root.querySelector('[data-chat-error]');
    const loading = root.querySelector('[data-chat-loading]');
    const status = root.querySelector('[data-chat-status]');
    const narrow = window.matchMedia('(max-width: 767px)');
    const templates = Object.fromEntries(['user-message', 'ai-message', 'step', 'session'].map((key) => [key, root.querySelector(`#chat-${key}-template`)]));
    const sessionTemplates = new Map([...history.children].map((node) => [node.dataset.chatSession, node.cloneNode(true)]));
    const timers = new Set();
    let activeId = sessions[0].id;
    let sequence = 0;

    const activeSession = () => sessions.find((session) => session.id === activeId);
    const localTime = () => `${new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', hourCycle: 'h23', timeZone: 'Asia/Jakarta' }).format(new Date()).replace('.', ':')} WIB`;
    const clone = (key) => templates[key].content.firstElementChild.cloneNode(true);
    const nextId = () => `chat-local-${++sequence}`;
    const nearBottom = () => feed.scrollHeight - feed.scrollTop - feed.clientHeight < 80;
    const scrollToBottom = () => { feed.scrollTop = feed.scrollHeight; };

    function setError(message = '') {
        error.textContent = message;
        error.hidden = !message;
        input.setAttribute('aria-invalid', String(Boolean(message)));
    }

    function syncComposer() {
        const pending = activeSession().pending;
        submit.disabled = pending || !input.value.trim();
        submit.setAttribute('aria-label', pending ? 'Menunggu jawaban demo' : 'Kirim pesan');
        loading.hidden = !pending;
        root.querySelectorAll('[data-chat-prompt]').forEach((button) => { button.disabled = pending; });
        input.style.height = 'auto';
        input.style.height = `${Math.min(120, Math.max(30, input.scrollHeight))}px`;
    }

    function setHistory(open) {
        const visible = !narrow.matches || open;
        historyBody.hidden = !visible;
        historyToggle.setAttribute('aria-expanded', String(visible));
    }

    function renderHistory() {
        const focusedSession = history.contains(document.activeElement) ? document.activeElement.dataset.chatSession : null;
        const keyword = historySearch.value.trim().toLocaleLowerCase('id');
        const matches = sessions.filter((session) => `${session.title} ${session.messages.map((message) => message.text).join(' ')}`.toLocaleLowerCase('id').includes(keyword));
        const fragment = document.createDocumentFragment();
        matches.forEach((session) => {
            const node = sessionTemplates.get(session.id)?.cloneNode(true) ?? clone('session');
            node.dataset.chatSession = session.id;
            node.title = session.title;
            node.setAttribute('aria-label', `Buka percakapan ${session.title}`);
            node.setAttribute('aria-pressed', String(session.id === activeId));
            node.classList.toggle('is-selected', session.id === activeId);
            node.querySelector('[data-session-title]').textContent = session.title;
            node.querySelector('[data-session-status]').textContent = session.pending ? 'Menyiapkan jawaban…' : session.status;
            node.querySelector('[data-session-time]').textContent = session.time;
            fragment.append(node);
        });
        history.replaceChildren(fragment);
        if (focusedSession) [...history.children].find((node) => node.dataset.chatSession === focusedSession)?.focus({ preventScroll: true });
        root.querySelector('[data-chat-session-count]').textContent = `${sessions.length} sesi`;
        root.querySelector('[data-chat-history-empty]').hidden = matches.length > 0;
    }

    function messageNode(message) {
        const node = clone(message.role === 'user' ? 'user-message' : 'ai-message');
        node.dataset.chatMessage = message.id;
        node.querySelector('[data-chat-text]').textContent = message.text;
        node.querySelector('[data-chat-time]').textContent = message.time;
        if (message.role !== 'assistant') return node;

        const tag = node.querySelector('[data-answer-tag]');
        tag.textContent = message.tag ?? '';
        tag.hidden = !message.tag;
        const guide = node.querySelector('[data-chat-guide]');
        guide.hidden = !message.steps?.length;
        node.querySelector('[data-guide-title]').textContent = message.guide ?? '';
        const steps = node.querySelector('[data-chat-steps]');
        message.steps?.forEach((step, index) => {
            const item = clone('step');
            const checkbox = item.querySelector('[data-chat-step]');
            checkbox.dataset.chatStep = index;
            checkbox.checked = Boolean(step.checked);
            item.querySelector('[data-step-title]').textContent = `${index + 1}. ${step.title}:`;
            item.querySelector('[data-step-body]').textContent = ` ${step.body}`;
            item.querySelector('.chat-code-block').hidden = !step.code;
            item.querySelector('[data-chat-code]').textContent = step.code ?? '';
            steps.append(item);
        });
        node.querySelector('[data-answer-feedback]').hidden = Boolean(message.welcome);
        node.querySelectorAll('[data-chat-feedback]').forEach((button) => button.setAttribute('aria-pressed', String(message.feedback === button.dataset.chatFeedback)));
        return node;
    }

    function showSession() {
        const session = activeSession();
        // Session changes replace the log without announcing an entire old conversation.
        feed.setAttribute('aria-live', 'off');
        messages.replaceChildren(...session.messages.map(messageNode));
        root.querySelector('[data-chat-date]').textContent = session.date;
        input.value = session.draft;
        setError();
        syncComposer();
        feed.scrollTop = session.scroll;
        requestAnimationFrame(() => feed.setAttribute('aria-live', 'polite'));
    }

    function rememberSession() {
        activeSession().draft = input.value;
        activeSession().scroll = feed.scrollTop;
    }

    function selectSession(id) {
        if (!sessions.some((session) => session.id === id)) return;
        rememberSession();
        activeId = id;
        renderHistory();
        showSession();
        setHistory(false);
        input.focus({ preventScroll: true });
        status.textContent = `Percakapan ${activeSession().title} dibuka.`;
    }

    function newSession() {
        rememberSession();
        const session = {
            id: nextId(), title: 'Percakapan Baru', status: 'Sesi demo baru', time: localTime(),
            date: `Sesi demo · ${new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Jakarta' }).format(new Date())}`,
            messages: [{ id: nextId(), role: 'assistant', text: data.welcome, time: localTime(), welcome: true }],
            draft: '', pending: false, scroll: 0,
        };
        sessions.unshift(session);
        historySearch.value = '';
        selectSession(session.id);
        status.textContent = 'Percakapan demo baru siap. Riwayat sesi sebelumnya tetap tersedia selama halaman ini terbuka.';
    }

    function replyTopic(question) {
        const text = question.toLocaleLowerCase('id');
        if (/vpn|globalprotect|504/.test(text)) return 'vpn';
        if (/password|sandi|sso|mfa|login|akun|akses folder/.test(text)) return 'password';
        if (/wi[\s-]?fi|internet|jaringan|koneksi/.test(text)) return 'wifi';
        if (/outlook|email|e-mail|sinkron|sync/.test(text)) return 'email';
        if (/printer|cetak/.test(text)) return 'printer';
        if (/software|lisensi|license|figma|office|aplikasi/.test(text)) return 'software';
        if (/laptop|hardware|monitor|baterai|docking|perangkat/.test(text)) return 'hardware';
        return 'general';
    }

    function sendMessage(rawText, preserveDraft = false) {
        const session = activeSession();
        if (session.pending) return;
        const text = rawText.trim();
        if (!text) { setError('Tulis pesan terlebih dahulu.'); input.focus(); return; }
        if (text.length > 2000) { setError('Pesan maksimal 2.000 karakter.'); input.focus(); return; }
        setError();
        const message = { id: nextId(), role: 'user', text, time: localTime() };
        if (!session.messages.some((item) => item.role === 'user')) session.title = text.length > 45 ? `${text.slice(0, 44)}…` : text;
        session.messages.push(message);
        session.status = 'Sedang aktif';
        session.time = message.time;
        session.pending = true;
        if (!preserveDraft) input.value = '';
        session.draft = input.value;
        messages.append(messageNode(message));
        renderHistory();
        syncComposer();
        scrollToBottom();
        status.textContent = 'Pesan ditambahkan. Copilot sedang menyiapkan jawaban simulasi.';

        // Capture this session, so changing chats cannot send a reply to the wrong log.
        const timer = setTimeout(() => {
            timers.delete(timer);
            const topic = replyTopic(text);
            const reply = { ...structuredClone(data.replies[topic]), id: nextId(), role: 'assistant', topic, time: localTime() };
            session.messages.push(reply);
            session.pending = false;
            session.time = reply.time;
            if (activeId === session.id) {
                const follow = nearBottom();
                messages.append(messageNode(reply));
                syncComposer();
                if (follow) scrollToBottom();
                status.textContent = 'Jawaban demo tersedia. Tidak ada pesan yang dikirim ke layanan AI.';
            }
            renderHistory();
        }, 1100);
        timers.add(timer);
    }

    input.addEventListener('input', () => {
        activeSession().draft = input.value;
        setError();
        syncComposer();
    });
    input.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' || event.shiftKey || event.isComposing || event.keyCode === 229) return;
        event.preventDefault();
        sendMessage(input.value);
    });
    root.querySelector('[data-chat-form]').addEventListener('submit', (event) => {
        event.preventDefault();
        sendMessage(input.value);
        input.focus({ preventScroll: true });
    });
    root.querySelectorAll('[data-new-chat]').forEach((button) => button.addEventListener('click', newSession));
    history.addEventListener('click', (event) => {
        const button = event.target.closest('[data-chat-session]');
        if (button) selectSession(button.dataset.chatSession);
    });
    historySearch.addEventListener('input', renderHistory);
    root.querySelector('[data-reset-chat-search]').addEventListener('click', () => {
        historySearch.value = '';
        renderHistory();
        historySearch.focus();
    });
    historyToggle.addEventListener('click', () => setHistory(historyBody.hidden));
    narrow.addEventListener('change', () => setHistory(false));
    root.querySelectorAll('[data-chat-prompt]').forEach((button) => button.addEventListener('click', () => {
        sendMessage(button.dataset.chatPrompt, true);
        setHistory(false);
        input.focus({ preventScroll: true });
    }));

    messages.addEventListener('change', (event) => {
        const checkbox = event.target.closest('[data-chat-step]');
        if (!checkbox) return;
        const message = activeSession().messages.find((item) => item.id === checkbox.closest('[data-chat-message]').dataset.chatMessage);
        message.steps[Number(checkbox.dataset.chatStep)].checked = checkbox.checked;
    });
    messages.addEventListener('click', async (event) => {
        const copy = event.target.closest('[data-copy-chat-code]');
        if (copy) {
            const code = copy.closest('.chat-code-block').querySelector('code').textContent;
            try {
                await navigator.clipboard.writeText(code);
                showToast('Perintah contoh disalin. Demo ini tidak menjalankan perintah tersebut.');
            } catch {
                showDialog('Salin perintah contoh', ['Browser tidak mengizinkan penyalinan otomatis. Pilih teks berikut untuk menyalinnya:', code]);
            }
            return;
        }
        const feedback = event.target.closest('[data-chat-feedback]');
        if (!feedback) return;
        const article = feedback.closest('[data-chat-message]');
        const message = activeSession().messages.find((item) => item.id === article.dataset.chatMessage);
        message.feedback = message.feedback === feedback.dataset.chatFeedback ? null : feedback.dataset.chatFeedback;
        article.querySelectorAll('[data-chat-feedback]').forEach((button) => button.setAttribute('aria-pressed', String(message.feedback === button.dataset.chatFeedback)));
        showToast(message.feedback ? 'Terima kasih. Penilaian demo dicatat hanya selama halaman ini terbuka.' : 'Penilaian demo dibatalkan.');
    });
    root.querySelectorAll('[data-chat-tool]').forEach((button) => button.addEventListener('click', () => {
        const voice = button.dataset.chatTool === 'voice';
        showDialog(voice ? 'Input suara · Demo' : 'Lampiran percakapan · Demo', [
            voice ? 'Input suara belum tersedia dalam demo ini. Silakan ketik pertanyaan di kolom pesan.' : 'Pengiriman gambar dan file dalam percakapan belum tersedia pada tahap ini. Anda dapat mencoba preview lampiran melalui halaman Buat Tiket Baru.',
            'Percakapan ini menggunakan respons simulasi, tanpa koneksi API AI atau n8n.',
        ]);
    }));

    window.addEventListener('pagehide', (event) => {
        if (!event.persisted) timers.forEach((timer) => clearTimeout(timer));
    });
    renderHistory();
    showSession();
    setHistory(false);

    return {
        search(query) {
            historySearch.value = query;
            setHistory(true);
            renderHistory();
            historySearch.focus();
            historySearch.scrollIntoView({ block: 'nearest', behavior: 'instant' });
        },
    };
}
