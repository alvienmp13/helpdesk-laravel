@extends('layouts.user')

@section('title', $ticket ? 'Edit Draft' : 'Buat Tiket Baru')

@section('content')
<div class="create-ticket-page">
    <header class="create-ticket-hero">
        <div><div class="create-service-meta"><x-portal.badge tone="blue" :dot="true">IT Service Desk Online</x-portal.badge><span>SLA Respons: &lt;15 Menit</span></div><h1>{{ $ticket ? 'Edit Draft Tiket' : 'Buat Tiket Bantuan IT' }}</h1><p>Sampaikan kendala teknis Anda. Tim IT HelpDesk atau AI Support akan segera membantu menyelesaikannya.</p></div>
        <a href="{{ route('user.tickets') }}" class="create-history-link"><x-portal.icon name="history" :size="15" />Riwayat Tiket Terakhir</a>
    </header>

    <noscript><p class="create-form-result">Formulir tetap dapat dikirim tanpa JavaScript.</p></noscript>

    <div class="create-ticket-grid">
        <form id="create-ticket-form" class="create-ticket-form" method="POST" action="{{ $ticket ? route('user.tickets.update', $ticket) : route('user.tickets.store') }}" enctype="multipart/form-data" novalidate data-create-ticket-form>
            @csrf
            @if($ticket) @method('PATCH') @endif
            <div class="create-error-summary" data-form-errors role="alert" hidden>Periksa kembali bagian formulir yang ditandai di bawah.</div>

            <x-portal.form-section number="1" title="Pilih Kategori Masalah" hint="Wajib dipilih">
                <fieldset class="category-options" aria-describedby="error-category_id"><legend class="sr-only">Kategori masalah, wajib dipilih</legend>
                    @foreach($categories as $category)
                        <label class="category-option"><input type="radio" name="category_id" value="{{ $category['value'] }}" class="sr-only" required @checked((string) $defaults['category_id'] === (string) $category['value'])><span class="category-tile"><span class="category-icon"><x-portal.icon :name="$category['icon']" :size="22" /></span><span class="category-name">{{ $category['label'] }}</span><span class="category-hint">{{ $category['hint'] }}</span></span></label>
                    @endforeach
                </fieldset>
                <p id="error-category_id" class="create-field-error" data-error-for="category_id" hidden></p>
            </x-portal.form-section>

            <x-portal.form-section number="2" title="Detail Subjek & Tingkat Urgensi">
                <div class="create-field-heading"><label for="issue-title" class="create-field-label">Subjek Ringkas Masalah</label><span>Ketik “VPN” untuk memicu AI Helper</span></div>
                <div class="create-input-wrap"><x-portal.icon name="edit" :size="16" /><input type="text" id="issue-title" name="title" value="{{ $defaults['title'] }}" placeholder="Tuliskan kendala utama yang Anda alami" minlength="5" maxlength="150" required aria-describedby="error-title"></div>
                <p id="error-title" class="create-field-error" data-error-for="title" hidden></p>

                <fieldset class="priority-fieldset" aria-describedby="error-priority"><legend class="create-field-label">Tingkat Dampak / Urgensi Operasional</legend><div class="priority-options">
                    @foreach($priorities as $priority)
                        <label class="priority-option priority-option-{{ $priority['value'] }}"><input type="radio" name="priority" value="{{ $priority['value'] }}" class="sr-only" required @checked($defaults['priority'] === $priority['value'])><span class="priority-tile"><span class="priority-tile-heading"><span class="priority-indicator"></span><strong>{{ $priority['label'] }}</strong><span class="priority-radio"></span></span><span class="priority-description">{{ $priority['description'] }}</span></span></label>
                    @endforeach
                </div></fieldset>
                <p id="error-priority" class="create-field-error" data-error-for="priority" hidden></p>

                <div class="create-location-fields">
                    <div><label for="issue-department" class="create-field-label">Departemen</label><div class="create-input-wrap create-select-wrap"><x-portal.icon name="building" :size="16" /><select id="issue-department" name="department" aria-describedby="error-department"><option value="">Pilih departemen</option>@foreach($departments as $department)<option @selected($defaults['department'] === $department)>{{ $department }}</option>@endforeach</select><x-portal.icon name="chevron-down" :size="13" /></div><p id="error-department" class="create-field-error" data-error-for="department" hidden></p></div>
                    <div><label for="issue-location" class="create-field-label">Lokasi / Gedung / Mode Kerja</label><div class="create-input-wrap create-select-wrap"><x-portal.icon name="pin" :size="16" /><select id="issue-location" name="location" aria-describedby="error-location"><option value="">Pilih lokasi kerja</option>@foreach($locations as $location)<option @selected($defaults['location'] === $location)>{{ $location }}</option>@endforeach</select><x-portal.icon name="chevron-down" :size="13" /></div><p id="error-location" class="create-field-error" data-error-for="location" hidden></p></div>
                </div>
            </x-portal.form-section>

            <x-portal.form-section number="3" title="Kronologi & Lampiran Berkas">
                <div class="create-field-heading"><label for="issue-description" class="create-field-label">Deskripsi Detail Masalah</label><button type="button" class="create-template-button" data-issue-template>Gunakan Template Masalah</button></div>
                <div class="description-editor"><div class="description-toolbar" role="group" aria-label="Format teks deskripsi"><button type="button" data-description-format="bold" aria-label="Tebalkan teks" title="Tebal (format Markdown)"><strong>B</strong></button><button type="button" data-description-format="italic" aria-label="Miringkan teks" title="Miring (format Markdown)"><em>I</em></button><span class="editor-separator"></span><button type="button" data-description-format="bullet" aria-label="Tambahkan daftar poin"><x-portal.icon name="list" :size="15" /></button><button type="button" data-description-format="number" aria-label="Tambahkan daftar bernomor"><x-portal.icon name="list-ordered" :size="15" /></button><button type="button" data-description-attach aria-label="Lampirkan gambar"><x-portal.icon name="image" :size="15" /></button></div><textarea id="issue-description" name="description" rows="5" minlength="20" maxlength="5000" required aria-describedby="error-description" placeholder="Jelaskan kronologi, pesan error, serta langkah yang sudah dicoba...">{{ $defaults['description'] }}</textarea></div>
                <p id="error-description" class="create-field-error" data-error-for="description" hidden></p>
                <x-portal.file-upload />
                @if($ticket && $ticket->attachments->isNotEmpty())<p class="upload-help">Lampiran draft yang tersimpan (maksimal total 5):</p>@foreach($ticket->attachments as $attachment)<p class="message-attachment"><a href="{{ route('tickets.attachments.download', $attachment) }}">{{ $attachment->original_name }}</a></p>@endforeach @endif
            </x-portal.form-section>

            <footer class="create-form-footer"><p><x-portal.icon name="shield" :size="17" /><span>Tiket dan draft tersimpan pada akun Anda.<br>Lampiran hanya dapat diakses pihak yang berhak.</span></p><div><button type="submit" name="intent" value="draft" class="create-draft-button" data-save-draft formnovalidate>Simpan Sebagai<br>Draft</button><button type="submit" name="intent" value="submit" class="create-submit-button" data-submit-ticket><x-portal.icon name="send" :size="17" /><span>Kirim Tiket<br>Bantuan</span></button></div></footer>
            <p class="create-form-result" data-create-result role="status" aria-live="polite" hidden></p>
        </form>

        <aside class="create-suggestion-column" aria-label="Saran solusi dan informasi IT">
            <section class="instant-suggestion-panel" aria-labelledby="instant-suggestion-title">
                <div class="instant-suggestion-heading"><span class="instant-suggestion-icon"><x-portal.icon name="bot" :size="20" /></span><div><h2 id="instant-suggestion-title">AI Instant Suggestion</h2><p>Saran lokal · simulasi</p></div><x-portal.badge tone="mint">Solusi Kilat</x-portal.badge></div>
                <p class="instant-suggestion-intro" data-suggestion-intro>Berdasarkan subjek “VPN”, berikut tiga langkah awal yang bisa dicoba. Saran ini menggunakan data simulasi.</p>
                <ol class="instant-suggestion-steps" data-suggestion-steps>@foreach($suggestions['vpn']['steps'] as $step)<li><span>{{ $loop->iteration }}</span><div><h3>{{ $step['title'] }}</h3><p>{{ $step['body'] }}</p></div></li>@endforeach</ol>
                <div class="instant-suggestion-actions"><button type="button" class="suggestion-resolved" data-suggestion-resolved><x-portal.icon name="check-circle" :size="14" />Solusi Mandiri Berhasil (Tutup)</button><button type="button" class="suggestion-continue" data-suggestion-continue><x-portal.icon name="arrow-right" :size="15" /><span>Kendala Belum Selesai, Tetap Ajukan Tiket</span></button></div>
                <p class="sr-only" data-suggestion-announcement role="status" aria-live="polite"></p>
            </section>

            <section class="create-related-articles" aria-labelledby="related-articles-title"><div><h2 id="related-articles-title">Pusat Informasi IT Terkait</h2><button type="button" data-all-related>Semua Artikel</button></div><ul data-related-articles>@foreach($suggestions['vpn']['articles'] as $article)<li><button type="button" data-related-article="{{ $loop->index }}"><x-portal.icon name="file" :size="14" /><span>{{ $article['title'] }}</span><x-portal.icon name="arrow-up-right" :size="12" /></button></li>@endforeach</ul></section>

            <div class="create-emergency"><span class="create-emergency-icon"><x-portal.icon name="headset" :size="18" /></span><div><h3>Kondisi Darurat?</h3><p>Hotline IT: Ext. 4410 (24/7)</p></div><button type="button" data-create-hotline>Hubungi</button></div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
    <script type="application/json" id="create-ticket-preview-data">{!! json_encode(['user' => $user, 'tickets' => [], 'defaults' => $defaults, 'categories' => $categories, 'priorities' => $priorities, 'departments' => $departments, 'locations' => $locations, 'suggestions' => $suggestions, 'demoAttachment' => $demoAttachment], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush
