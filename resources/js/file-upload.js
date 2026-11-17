const MAX_BYTES = 5 * 1024 * 1024;
const MAX_FILES = 5;

export function initFileUpload(root, { demo, showDialog }) {
    const input = root.querySelector('[data-upload-input]');
    const dropzone = root.querySelector('[data-upload-dropzone]');
    const list = root.querySelector('[data-upload-list]');
    const errors = root.querySelector('[data-upload-errors]');
    const template = root.querySelector('[data-upload-template]');
    const status = root.querySelector('[data-upload-status]');
    let entries = demo ? [{ ...demo, id: 'demo', url: null }] : [];
    let sequence = 0;
    let dragDepth = 0;

    const sizeLabel = (size) => size >= 1024 * 1024 ? `${(size / (1024 * 1024)).toFixed(1)} MB` : `${Math.max(1, Math.ceil(size / 1024))} KB`;

    function showErrors(messages = []) {
        errors.replaceChildren();
        messages.forEach((message) => {
            const item = document.createElement('li');
            item.textContent = message;
            errors.append(item);
        });
        errors.hidden = !messages.length;
        input.setAttribute('aria-invalid', String(messages.length > 0));
    }

    function preview(entry) {
        if (entry.demo) {
            showDialog('Contoh lampiran', [entry.name, 'Baris ini adalah contoh tampilan dari referensi desain. Belum ada file yang dipilih atau diunggah. Pilih berkas Anda sendiri untuk mencoba preview lokal.']);
            return;
        }
        const content = showDialog('Pratinjau lampiran lokal', [entry.name, `${sizeLabel(entry.size)} · File akan diunggah saat tiket atau draft disimpan.`]);
        if (entry.kind === 'image') {
            const image = document.createElement('img');
            image.src = entry.url;
            image.alt = `Pratinjau ${entry.name}`;
            image.className = 'local-file-image';
            image.addEventListener('error', () => {
                image.remove();
                const message = document.createElement('p');
                message.textContent = 'Gambar tidak dapat dibaca. Pastikan berkas PNG/JPG tidak rusak.';
                content.append(message);
            }, { once: true });
            content.append(image);
        } else if (entry.kind === 'log') {
            const pre = document.createElement('pre');
            pre.className = 'local-file-log';
            pre.textContent = 'Membaca pratinjau log…';
            content.append(pre);
            entry.file.slice(0, 20000).text().then((text) => {
                pre.textContent = text + (entry.size > 20000 ? '\n\n[Pratinjau dibatasi sampai 20 KB.]' : '');
            }).catch(() => { pre.textContent = 'Berkas log tidak dapat dibaca. Silakan pilih ulang.'; });
        }
        const link = document.createElement('a');
        link.href = entry.url;
        link.className = 'local-file-link';
        if (entry.kind === 'pdf') {
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.textContent = 'Buka pratinjau PDF di tab baru';
        } else {
            link.download = entry.name;
            link.textContent = 'Unduh salinan berkas lokal';
        }
        content.append(link);
    }

    function render() {
        const transfer = new DataTransfer();
        entries.filter((entry) => entry.file).forEach((entry) => transfer.items.add(entry.file));
        input.files = transfer.files;
        list.replaceChildren();
        entries.forEach((entry) => {
            const row = template.content.cloneNode(true);
            row.querySelector('[data-file-name]').textContent = entry.name;
            row.querySelector('[data-file-meta]').textContent = `${sizeLabel(entry.size)} · ${entry.demo ? 'Contoh lampiran, belum diunggah' : 'Preview lokal, belum diunggah'}`;
            const thumbnail = row.querySelector('[data-file-thumbnail]');
            if (entry.kind === 'image') {
                thumbnail.src = entry.url;
                thumbnail.hidden = false;
                thumbnail.addEventListener('error', () => { thumbnail.hidden = true; }, { once: true });
            }
            const open = row.querySelector('[data-file-preview]');
            open.setAttribute('aria-label', `Pratinjau ${entry.name}`);
            open.addEventListener('click', () => preview(entry));
            const remove = row.querySelector('[data-file-remove]');
            remove.setAttribute('aria-label', `Hapus ${entry.name}`);
            remove.addEventListener('click', () => {
                if (entry.url) URL.revokeObjectURL(entry.url);
                entries = entries.filter((item) => item.id !== entry.id);
                showErrors();
                render();
                status.textContent = `${entry.name} dihapus dari lampiran lokal.`;
                input.focus();
            });
            list.append(row);
        });
    }

    function addFiles(files) {
        const messages = [];
        let accepted = 0;
        for (const file of files) {
            const extension = file.name.split('.').pop().toLowerCase();
            const types = {
                png: ['image/png', ''], jpg: ['image/jpeg', ''], jpeg: ['image/jpeg', ''],
                pdf: ['application/pdf', ''], log: ['text/plain', 'application/octet-stream', ''],
            };
            if (!Object.hasOwn(types, extension) || !types[extension].includes(file.type)) {
                messages.push(`${file.name}: tipe file tidak sesuai. Gunakan PNG, JPG, PDF, atau LOG; file tidak ditambahkan.`);
                continue;
            }
            if (!file.size || file.size > MAX_BYTES) {
                messages.push(`${file.name}: ${file.size ? 'ukuran maksimal 5 MB per file' : 'file kosong tidak dapat dilampirkan'}; file tidak ditambahkan.`);
                continue;
            }
            const key = `${file.name}:${file.size}:${file.lastModified}`;
            if (entries.some((entry) => entry.key === key)) {
                messages.push(`${file.name}: berkas yang sama sudah ada dalam daftar.`);
                continue;
            }
            if (entries.filter((entry) => !entry.demo).length >= MAX_FILES) {
                messages.push(`${file.name}: maksimal ${MAX_FILES} lampiran. Hapus salah satu berkas untuk menambah file lain.`);
                continue;
            }
            entries = entries.filter((entry) => !entry.demo);
            entries.push({ id: ++sequence, key, file, name: file.name, size: file.size, kind: extension === 'pdf' ? 'pdf' : extension === 'log' ? 'log' : 'image', url: URL.createObjectURL(file), demo: false });
            accepted++;
        }
        input.value = '';
        showErrors(messages);
        render();
        status.textContent = `${accepted} berkas ditambahkan untuk preview lokal. ${entries.filter((entry) => !entry.demo).length} lampiran terpilih.`;
    }

    input.addEventListener('change', () => addFiles([...input.files]));
    dropzone.addEventListener('dragenter', (event) => { event.preventDefault(); dragDepth++; dropzone.classList.add('is-dragging'); });
    dropzone.addEventListener('dragover', (event) => { event.preventDefault(); event.dataTransfer.dropEffect = 'copy'; });
    dropzone.addEventListener('dragleave', (event) => { event.preventDefault(); dragDepth = Math.max(0, dragDepth - 1); if (!dragDepth) dropzone.classList.remove('is-dragging'); });
    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        dragDepth = 0;
        dropzone.classList.remove('is-dragging');
        addFiles([...event.dataTransfer.files]);
    });
    window.addEventListener('pagehide', (event) => { if (!event.persisted) entries.forEach((entry) => { if (entry.url) URL.revokeObjectURL(entry.url); }); });
    render();

    return {
        names: () => entries.filter((entry) => !entry.demo).map((entry) => entry.name),
        openPicker: () => input.click(),
        clear() {
            entries.forEach((entry) => { if (entry.url) URL.revokeObjectURL(entry.url); });
            entries = [];
            input.value = '';
            showErrors();
            render();
        },
    };
}
