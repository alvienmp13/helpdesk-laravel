@extends('layouts.user')

@section('title', 'Chat dengan AI')

@section('content')
<div class="chat-page">
    <header class="chat-page-heading">
        <span class="chat-brand-icon"><x-portal.icon name="bot" :size="27" /></span>
        <div class="chat-heading-copy"><div><h1>HelpDesk AI Assistant</h1><x-portal.badge tone="mint" :dot="true">Online 24/7</x-portal.badge></div><p>Siap membantu kendala teknis, konfigurasi sistem, dan panduan layanan IT karyawan</p></div>
        <div class="chat-heading-actions"><span class="chat-mode-badge"><x-portal.icon name="shield" :size="13" />Mode Demo · Respons Simulasi</span><button type="button" class="chat-new-top" data-new-chat><x-portal.icon name="plus" :size="14" />Percakapan Baru</button></div>
    </header>

    <noscript><p class="create-error-summary">Aktifkan JavaScript untuk memilih sesi, mengirim pesan, dan mencoba balasan demo. Riwayat awal di bawah tetap dapat dibaca.</p></noscript>

    <div class="chat-workspace">
        <aside class="chat-history-column" aria-label="Riwayat dan kategori percakapan">
            <section class="chat-history-panel">
                <div class="chat-history-controls"><button type="button" class="chat-new-session" data-new-chat><x-portal.icon name="chat" :size="15" /><span>Mulai Sesi Baru</span><x-portal.icon name="chevron-right" :size="14" /></button><button type="button" class="chat-history-toggle" data-history-toggle aria-expanded="true" aria-controls="chat-history-body"><x-portal.icon name="history" :size="17" /><span>Riwayat</span><x-portal.icon name="chevron-down" :size="13" /></button></div>
                <div id="chat-history-body" class="chat-history-body">
                    <label class="chat-history-search"><x-portal.icon name="search" :size="13" /><input type="search" placeholder="Cari percakapan..." aria-label="Cari riwayat percakapan" autocomplete="off" data-chat-search></label>
                    <div class="chat-history-heading"><h2>Riwayat Sesi AI</h2><span data-chat-session-count>{{ count($sessions) }} sesi</span></div>
                    <div class="chat-history-list" data-chat-history>@foreach($sessions as $session)<x-portal.chat-session :session="$session" :selected="$loop->first" />@endforeach</div>
                    <div class="chat-history-empty" data-chat-history-empty hidden><p>Tidak ada sesi yang cocok.</p><button type="button" data-reset-chat-search>Reset pencarian</button></div>
                    <section class="chat-popular-categories" aria-labelledby="chat-categories-title"><h2 id="chat-categories-title">Kategori Bantuan Populer</h2>@foreach($categories as $category)<button type="button" class="chat-category chat-category-{{ $category['tone'] }}" data-chat-prompt="{{ $category['prompt'] }}"><x-portal.icon :name="$category['icon']" :size="16" /><span><strong>{{ $category['title'] }}</strong><small>{{ $category['description'] }}</small></span></button>@endforeach</section>
                </div>
            </section>
            <div class="chat-demo-card"><x-portal.icon name="sparkles" :size="23" /><div><h2>AI Copilot v2.4 (Demo)</h2><p>Simulasi panduan bantuan IT.<br>Tanpa koneksi API atau n8n.</p></div></div>
        </aside>

        <section class="chat-window" aria-label="Percakapan dengan AI demo">
            <div id="chat-message-feed" class="chat-message-feed" role="log" aria-label="Pesan percakapan aktif" aria-live="polite" aria-relevant="additions" tabindex="0">
                <div class="chat-date-divider"><span data-chat-date>{{ $sessions[0]['date'] }}</span></div>
                <div class="chat-message-list" data-chat-messages>@foreach($sessions[0]['messages'] as $message)<x-portal.chat-message :message="$message" :user="$user" />@endforeach</div>
                <div class="chat-typing-indicator" data-chat-loading hidden><span class="chat-message-avatar"><x-portal.icon name="bot" :size="18" /></span><div><span class="chat-typing-dots" aria-hidden="true"><i></i><i></i><i></i></span><span>Copilot sedang menyiapkan jawaban demo…</span></div></div>
            </div>

            <div class="chat-recommendations" aria-label="Saran pertanyaan"><span class="chat-recommendations-label"><x-portal.icon name="lightbulb" :size="12" />Saran Pertanyaan:</span>@foreach($recommendations as $recommendation)<button type="button" data-chat-prompt="{{ $recommendation }}">{{ $recommendation }}</button>@endforeach</div>
            <form class="chat-composer" data-chat-form novalidate>
                <div class="chat-composer-row"><div class="chat-composer-tools"><button type="button" data-chat-tool="attachment" aria-label="Informasi lampiran pada demo" title="Lampiran (demo)"><x-portal.icon name="paperclip" :size="17" /></button><button type="button" data-chat-tool="image" aria-label="Informasi gambar pada demo" title="Gambar (demo)"><x-portal.icon name="image" :size="17" /></button></div><label for="chat-input" class="sr-only">Pesan untuk AI demo</label><textarea id="chat-input" name="message" rows="1" maxlength="2000" placeholder="Ketik pertanyaan atau kendala IT Anda... (Tekan Enter untuk kirim)" aria-describedby="chat-input-help chat-input-error" data-chat-input></textarea><button type="button" class="chat-voice-button" data-chat-tool="voice" aria-label="Informasi input suara pada demo" title="Input suara (demo)"><x-portal.icon name="mic" :size="17" /></button><button type="submit" class="chat-send-button" aria-label="Kirim pesan" data-chat-send disabled><x-portal.icon name="send" :size="21" /></button></div>
                <p id="chat-input-error" class="chat-input-error" data-chat-error role="alert" hidden></p>
                <div id="chat-input-help" class="chat-composer-footer"><span><x-portal.icon name="info" :size="11" />Jawaban demo · tidak dikirim ke AI</span><span>Riwayat hanya selama halaman terbuka</span></div>
            </form>
            <span class="sr-only" data-chat-status role="status" aria-live="polite"></span>
        </section>
    </div>

    <template id="chat-user-message-template"><x-portal.chat-message :message="['id' => '', 'role' => 'user', 'text' => '', 'time' => '']" :user="$user" /></template>
    <template id="chat-ai-message-template"><x-portal.chat-message :message="['id' => '', 'role' => 'assistant', 'text' => '', 'time' => '']" :user="$user" /></template>
    <template id="chat-step-template"><x-portal.chat-step :step="['title' => '', 'body' => '']" /></template>
    <template id="chat-session-template"><x-portal.chat-session :session="['id' => '', 'title' => '', 'status' => '', 'time' => '', 'icon' => 'chat']" /></template>
</div>
@endsection

@push('scripts')
    <script type="application/json" id="chat-preview-data">{!! json_encode(['user' => $user, 'tickets' => [], 'sessions' => $sessions, 'replies' => $replies, 'welcome' => $welcome], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush
