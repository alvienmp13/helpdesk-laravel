import './bootstrap';
import './auth';
import { initTickets } from './tickets';
import { initTicketCreate } from './ticket-create';
import { initChat } from './chat';

const previewData = document.querySelector('#dashboard-preview-data, #tickets-preview-data, #create-ticket-preview-data, #chat-preview-data');

if (previewData) {
    const data = JSON.parse(previewData.textContent);
    const { user, tickets } = data;
    const sidebar = document.querySelector('#portal-sidebar');
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const dialog = document.querySelector('[data-info-dialog]');
    const toast = document.querySelector('[data-toast]');
    const notifications = document.querySelector('#notification-panel');
    const notificationToggle = document.querySelector('[data-notification-toggle]');
    const mobileViewport = window.matchMedia('(max-width: 1023px)');
    let toastTimeout;

    const guides = [
        { key: 'vpn', title: 'Cara hubungkan VPN kantor', body: 'Buka aplikasi VPN perusahaan, pilih profil kantor, lalu masuk menggunakan akun SSO dan verifikasi MFA. Jika koneksi gagal, periksa koneksi internet Anda dan hubungi tim IT.' },
        { key: 'password', title: 'Reset kata sandi email SSO', body: 'Buka portal SSO perusahaan dan pilih “Lupa kata sandi”. Ikuti verifikasi identitas melalui metode pemulihan yang terdaftar. Jangan bagikan kata sandi atau kode OTP kepada siapa pun.' },
        { key: 'software', title: 'Request software baru', body: 'Siapkan nama software, kebutuhan pekerjaan, dan persetujuan manajer. Permintaan nantinya dapat diajukan melalui formulir tiket dengan kategori Akses Perangkat Lunak.' },
    ];

    function setNavigation(open, restoreFocus = false) {
        sidebar.classList.toggle('is-open', open);
        sidebarToggle.setAttribute('aria-expanded', String(open));
        sidebarToggle.setAttribute('aria-label', open ? 'Tutup navigasi' : 'Buka navigasi');
        backdrop.hidden = !open;
        document.body.classList.toggle('navigation-open', open);
        sidebar.inert = mobileViewport.matches && !open;
        document.querySelector('.portal-workspace').inert = open;
        if (open) sidebar.querySelector('[data-sidebar-close]').focus();
        else if (restoreFocus) sidebarToggle.focus();
    }

    function showToast(message) {
        clearTimeout(toastTimeout);
        toast.textContent = message;
        toast.hidden = false;
        toastTimeout = setTimeout(() => { toast.hidden = true; }, 5500);
    }

    // All dynamic content is inserted as text, including search and AI input.
    function showDialog(title, paragraphs = [], articles = []) {
        setNavigation(false);
        document.querySelector('[data-dialog-title]').textContent = title;
        const content = document.querySelector('[data-dialog-content]');
        content.replaceChildren();
        paragraphs.forEach((text) => {
            const paragraph = document.createElement('p');
            paragraph.textContent = text;
            content.append(paragraph);
        });
        articles.forEach(({ title: articleTitle, body }) => {
            const article = document.createElement('article');
            const heading = document.createElement('h3');
            const paragraph = document.createElement('p');
            heading.textContent = articleTitle;
            paragraph.textContent = body;
            article.append(heading, paragraph);
            content.append(article);
        });
        dialog.showModal();
        return content;
    }

    function answerQuestion(question, key) {
        const normalized = question.toLowerCase();
        const guide = guides.find((item) => item.key === key)
            ?? guides.find((item) => normalized.includes(item.key))
            ?? (/sandi|sso|email/.test(normalized) ? guides[1] : null);
        const answer = document.querySelector('[data-ai-answer]');
        const label = document.createElement('strong');
        const text = document.createElement('p');
        label.textContent = 'Pratinjau jawaban · data dummy';
        text.textContent = guide?.body ?? 'Pertanyaan Anda sudah ditampilkan dalam pratinjau. Untuk bantuan lebih lanjut, siapkan detail kendala, waktu kejadian, dan tangkapan layar. Chat AI langsung belum tersedia pada tahap ini.';
        answer.replaceChildren(label, text);
        answer.hidden = false;
    }

    function setNotifications(open) {
        notifications.hidden = !open;
        notificationToggle.setAttribute('aria-expanded', String(open));
    }

    const actions = {
        knowledge: () => showDialog('Pusat Bantuan & FAQ', ['Panduan contoh untuk pratinjau portal pengguna.'], guides),
        settings: () => showDialog('Pengaturan Akun', ['Pengaturan akun akan tersedia pada tahap berikutnya.', `Akun: ${user.name} · ${user.role}`]),
        profile: () => showDialog('Profil Pengguna', [`${user.name} · ${user.role}`, 'Anda sedang masuk menggunakan akun HelpDesk. Tiket tersimpan pada akun Anda. Fitur AI dan perangkat masih berupa contoh.']),
        hotline: () => showDialog('IT Hotline Darurat', ['Ext. 104 / +62 21–5088–0104', 'Nomor kontak ini adalah data contoh sesuai referensi desain. Kontak operasional akan disesuaikan pada tahap implementasi layanan.']),
        location: () => showDialog('Walk-in Tech Bar', ['Lantai 4, Wing B · Jam kerja IT: 08:00–18:00 WIB.', 'Lokasi dan jam layanan merupakan data contoh untuk pratinjau.']),
    };

    const ticketPage = initTickets({ tickets, user, showToast });
    initTicketCreate({ data, showDialog, showToast });
    const chatPage = initChat({ data, showDialog, showToast });

    sidebarToggle.addEventListener('click', () => setNavigation(!sidebar.classList.contains('is-open')));
    document.querySelector('[data-sidebar-close]').addEventListener('click', () => setNavigation(false, true));
    backdrop.addEventListener('click', () => setNavigation(false, true));
    mobileViewport.addEventListener('change', () => setNavigation(false));
    setNavigation(false);

    document.querySelectorAll('[data-action]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            actions[button.dataset.action]?.();
        });
    });

    document.querySelectorAll('[data-close-navigation]').forEach((link) => {
        link.addEventListener('click', () => {
            setNavigation(false);
            const target = document.querySelector(link.getAttribute('href'));
            target.setAttribute('tabindex', '-1');
            target.focus({ preventScroll: true });
        });
    });

    document.querySelector('[data-search-form]').addEventListener('submit', (event) => {
        event.preventDefault();
        const query = document.querySelector('[data-search-input]').value.trim();
        if (!query) return showToast('Masukkan kata kunci tiket atau panduan yang ingin dicari.');
        if (ticketPage) {
            ticketPage.search(query);
            return;
        }
        if (chatPage) {
            chatPage.search(query);
            return;
        }
        const url = new URL(document.querySelector('[data-search-form]').action);
        url.searchParams.set('q', query);
        window.location.assign(url);
    });

    document.querySelector('[data-ai-form]')?.addEventListener('submit', (event) => {
        event.preventDefault();
        const input = document.querySelector('#ai-question');
        if (!input.value.trim()) return input.focus();
        answerQuestion(input.value.trim());
    });

    document.querySelectorAll('[data-ai-prompt]').forEach((button) => {
        button.addEventListener('click', () => {
            const guide = guides.find((item) => item.key === button.dataset.aiPrompt);
            document.querySelector('#ai-question').value = guide.title;
            answerQuestion(guide.title, guide.key);
        });
    });

    notificationToggle.addEventListener('click', () => setNotifications(notifications.hidden));
    document.querySelectorAll('[data-close-notifications]').forEach((link) => link.addEventListener('click', () => setNotifications(false)));
    document.addEventListener('click', (event) => {
        if (!notifications.contains(event.target) && !notificationToggle.contains(event.target)) setNotifications(false);
    });
    document.querySelector('[data-dialog-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (event) => {
        const bounds = dialog.getBoundingClientRect();
        if (event.target === dialog && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) dialog.close();
    });
    dialog.addEventListener('close', () => {
        if (mobileViewport.matches && sidebar.contains(document.activeElement)) sidebarToggle.focus();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            if (sidebar.classList.contains('is-open')) setNavigation(false, true);
            if (!notifications.hidden) { setNotifications(false); notificationToggle.focus(); }
        }
        if (event.key === 'Tab' && sidebar.classList.contains('is-open')) {
            const focusable = [...sidebar.querySelectorAll('a[href], button')].filter((element) => element.getClientRects().length);
            const first = focusable[0];
            const last = focusable.at(-1);
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
}
