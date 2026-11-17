import { initFileUpload } from './file-upload';

const FIELD_NAMES = ['category_id', 'title', 'priority', 'department', 'location', 'description'];

export function initTicketCreate({ data, showDialog, showToast }) {
    const root = document.querySelector('.create-ticket-page');
    if (!root) return;


    const form = root.querySelector('[data-create-ticket-form]');
    const description = form.elements.namedItem('description');
    const result = root.querySelector('[data-create-result]');
    const articleTemplate = root.querySelector('[data-related-article]').closest('li').cloneNode(true);
    const uploader = initFileUpload(root.querySelector('[data-file-upload]'), { demo: data.demoAttachment, showDialog });
    let suggestionKey = 'vpn';
    let suggestionTimer;

    function values() {
        const fields = new FormData(form);
        return Object.fromEntries(FIELD_NAMES.map((name) => [name, String(fields.get(name) ?? '').trim()]));
    }

    function setFieldError(name, message = '') {
        const error = root.querySelector(`[data-error-for="${name}"]`);
        error.textContent = message;
        error.hidden = !message;
        form.querySelectorAll(`[name="${name}"]`).forEach((field) => field.setAttribute('aria-invalid', String(Boolean(message))));
    }

    function updateSuggestions() {
        const fields = values();
        const slug = data.categories.find((item) => String(item.value) === fields.category_id)?.slug;
        const category = Object.hasOwn(data.suggestions, slug) ? slug : 'network';
        const nextKey = category === 'network' && /vpn|globalprotect/i.test(fields.title) ? 'vpn' : category;
        const changed = suggestionKey !== nextKey;
        suggestionKey = nextKey;
        const suggestion = data.suggestions[suggestionKey];
        root.querySelector('[data-suggestion-intro]').textContent = `Berdasarkan ${suggestionKey === 'vpn' ? 'subjek' : 'kategori'} “${suggestion.keyword}”, berikut tiga langkah awal yang bisa dicoba. Saran ini menggunakan data simulasi.`;
        const steps = root.querySelector('[data-suggestion-steps]');
        steps.replaceChildren();
        suggestion.steps.forEach((step, index) => {
            const item = document.createElement('li');
            const number = document.createElement('span');
            const content = document.createElement('div');
            const heading = document.createElement('h3');
            const text = document.createElement('p');
            number.textContent = String(index + 1);
            heading.textContent = step.title;
            text.textContent = step.body;
            content.append(heading, text);
            item.append(number, content);
            steps.append(item);
        });
        const articles = root.querySelector('[data-related-articles]');
        articles.replaceChildren();
        suggestion.articles.forEach((article, index) => {
            const item = articleTemplate.cloneNode(true);
            const button = item.querySelector('button');
            button.dataset.relatedArticle = String(index);
            button.title = article.title;
            button.querySelector('span').textContent = article.title;
            articles.append(item);
        });
        if (changed) root.querySelector('[data-suggestion-announcement]').textContent = `Saran simulasi diperbarui untuk ${suggestion.keyword}.`;
    }

    form.addEventListener('input', (event) => {
        if (FIELD_NAMES.includes(event.target.name)) {
            setFieldError(event.target.name);
            root.querySelector('[data-form-errors]').hidden = [...root.querySelectorAll('[data-error-for]')].every((error) => error.hidden);
            result.hidden = true;
        }
        if (event.target.name === 'title') {
            clearTimeout(suggestionTimer);
            suggestionTimer = setTimeout(updateSuggestions, 250);
        }
    });
    form.addEventListener('change', (event) => {
        if (FIELD_NAMES.includes(event.target.name)) setFieldError(event.target.name);
        if (event.target.name === 'category_id') { clearTimeout(suggestionTimer); updateSuggestions(); }
    });

    root.querySelector('[data-issue-template]').addEventListener('click', () => {
        const template = '\n\nWaktu mulai kendala:\n\nLangkah yang sudah dicoba:\n1. \n\nDampak pada pekerjaan:\n\nPesan error / kode kendala:\n';
        if (description.value.includes('Langkah yang sudah dicoba:')) {
            showToast('Template sudah ada di deskripsi. Silakan lengkapi bagian yang tersedia.');
        } else if (description.value.length + template.length > 5000) {
            showToast('Ruang deskripsi tidak cukup. Ringkas teks sebelum menambahkan template.');
        } else {
            description.value += template;
            description.dispatchEvent(new Event('input', { bubbles: true }));
        }
        description.focus();
    });

    root.querySelectorAll('[data-description-format]').forEach((button) => {
        button.addEventListener('click', () => {
            const start = description.selectionStart;
            const end = description.selectionEnd;
            const selected = description.value.slice(start, end);
            const formats = { bold: `**${selected || 'teks tebal'}**`, italic: `_${selected || 'teks miring'}_`, bullet: `\n${(selected || 'Langkah yang dicoba').split('\n').map((line) => `- ${line}`).join('\n')}`, number: `\n${(selected || 'Langkah pertama').split('\n').map((line, index) => `${index + 1}. ${line}`).join('\n')}` };
            const insertion = formats[button.dataset.descriptionFormat];
            if (description.value.length - (end - start) + insertion.length > 5000) return showToast('Deskripsi maksimal 5.000 karakter.');
            description.setRangeText(insertion, start, end, 'end');
            description.dispatchEvent(new Event('input', { bubbles: true }));
            description.focus();
        });
    });
    root.querySelector('[data-description-attach]').addEventListener('click', () => uploader.openPicker());
    root.querySelector('[data-suggestion-resolved]').addEventListener('click', () => {
        showDialog('Solusi mandiri berhasil', ['Jika kendala sudah teratasi, Anda dapat kembali ke daftar tiket. Tiket baru dibuat setelah Anda memilih Kirim Tiket.']);
    });
    root.querySelector('[data-suggestion-continue]').addEventListener('click', () => {
        root.querySelector('.create-form-footer').scrollIntoView({ block: 'center', behavior: 'instant' });
        root.querySelector('[data-submit-ticket]').focus({ preventScroll: true });
        showToast('Lengkapi formulir, lalu pilih Kirim Tiket Bantuan untuk mengirim laporan ke tim IT.');
    });
    root.querySelector('[data-related-articles]').addEventListener('click', (event) => {
        const button = event.target.closest('[data-related-article]');
        if (!button) return;
        const article = data.suggestions[suggestionKey].articles[Number(button.dataset.relatedArticle)];
        if (article) showDialog(article.title, ['Artikel contoh untuk pratinjau bantuan IT.', article.body]);
    });
    root.querySelector('[data-all-related]').addEventListener('click', () => showDialog('Pusat Informasi IT Terkait', ['Artikel contoh sesuai kategori yang dipilih.'], data.suggestions[suggestionKey].articles));
    root.querySelector('[data-create-hotline]').addEventListener('click', () => showDialog('Kontak IT darurat — simulasi', ['Hotline IT: Ext. 4410 (24/7).', 'Nomor dan jam layanan adalah data contoh sesuai referensi desain. Tidak ada panggilan yang dilakukan.']));
    root.querySelector('[data-save-draft]').disabled = false;
    root.querySelector('[data-submit-ticket]').disabled = false;

    updateSuggestions();
}
