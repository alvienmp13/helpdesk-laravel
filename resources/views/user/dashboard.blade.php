@extends('layouts.user')

@section('title', 'Beranda')

@section('content')
    <section class="welcome-section" aria-labelledby="welcome-title">
        <div class="welcome-copy">
            <h1 id="welcome-title">Selamat datang kembali, <span class="whitespace-nowrap">{{ $user['first_name'] }}! <span class="welcome-wave" aria-hidden="true">👋</span></span></h1>
            <p>Pusat bantuan IT mandiri. Butuh bantuan perangkat lunak, hardware, atau akses akun hari ini?</p>
        </div>
        <div class="welcome-status">
            <span class="system-status"><span class="status-dot" aria-hidden="true"></span><span>Sistem Operasional<br> Normal</span></span>
            <span class="working-hours"><x-portal.icon name="clock" :size="15" /><span>Jam Kerja IT: 08:00 – 18:00 WIB</span></span>
        </div>
    </section>

    <section class="service-grid" aria-label="Layanan bantuan IT">
        @foreach($services as $service)
            <x-portal.service-card :service="$service" />
        @endforeach
    </section>

    <section class="ticket-summary" aria-labelledby="summary-title">
        <div class="section-heading summary-heading">
            <div class="flex flex-wrap items-center gap-2"><h2 id="summary-title">Ringkasan Tiket Saya</h2><span class="account-badge">Akun: {{ $user['name'] }}</span></div>
            <a href="{{ route('user.tickets') }}" class="text-link">Lihat Semua Riwayat<x-portal.icon name="chevron-right" :size="12" /></a>
        </div>
        <div class="stats-grid">
            @foreach($statistics as $stat)
                <x-portal.stat-card :stat="$stat" />
            @endforeach
        </div>
    </section>

    <div class="dashboard-detail-grid">
        <div class="min-w-0">
            <section id="active-tickets" class="active-tickets" aria-labelledby="active-tickets-title">
                <div class="section-heading active-heading">
                    <div><h2 id="active-tickets-title">Tiket Aktif Saya</h2><p>Tiket yang sedang berlangsung dan membutuhkan pemantauan Anda.</p></div>
                    <x-portal.badge tone="blue">{{ $activeCount }} Tiket Aktif</x-portal.badge>
                </div>
                <div class="space-y-3.5">
                    @forelse($tickets as $ticket)
                        <x-portal.ticket-card :ticket="$ticket" />
                    @empty<p class="ticket-description">Belum ada tiket aktif. Buat tiket baru jika Anda memerlukan bantuan.</p>@endforelse
                </div>
            </section>

            <section class="registered-devices" aria-labelledby="devices-title">
                <h2 id="devices-title" class="eyebrow">Perangkat Terdaftar Milik Anda</h2>
                <div class="devices-grid">
                    @foreach($devices as $device)
                        <div class="device-card"><span class="device-icon device-{{ $device['tone'] }}"><x-portal.icon :name="$device['icon']" :size="22" /></span><div class="min-w-0"><h3>{{ $device['name'] }}</h3><p>{{ $device['detail'] }}</p></div></div>
                    @endforeach
                </div>
            </section>
        </div>

        <aside class="support-column" aria-label="Bantuan instan dan kontak IT">
            <section id="ai-support" class="ai-panel" aria-labelledby="ai-title">
                <div class="ai-heading"><span class="ai-icon"><x-portal.icon name="headset" :size="21" /></span><div><h2 id="ai-title">Tanya Solusi Instan AI</h2><p>Copilot IT HelpDesk 24/7</p></div><x-portal.icon name="bolt" :size="17" class="ai-bolt" /></div>
                <p class="ai-description">Ajukan pertanyaan teknis Anda. AI akan mencarikan panduan resmi perusahaan dalam hitungan detik.</p>
                <form class="ai-input" data-ai-form><label for="ai-question" class="sr-only">Kendala teknis Anda</label><input id="ai-question" name="question" placeholder="Ketik kendala Anda di sini..." maxlength="500" required autocomplete="off"><button type="submit" aria-label="Kirim pertanyaan"><x-portal.icon name="send" :size="15" /></button></form>
                <div class="ai-answer" data-ai-answer role="status" hidden></div>
                <p class="prompt-label">Prompt Populer & Cepat:</p>
                <div class="space-y-2">
                    @foreach($prompts as $prompt)
                        <button type="button" class="prompt-button prompt-{{ $prompt['key'] }}" data-ai-prompt="{{ $prompt['key'] }}"><x-portal.icon :name="$prompt['icon']" :size="15" /><span>{{ $prompt['label'] }}</span><x-portal.icon name="chevron-right" :size="12" /></button>
                    @endforeach
                </div>
            </section>

            <section id="urgent-help" class="urgent-panel" aria-labelledby="urgent-title">
                <h2 id="urgent-title">Butuh Bantuan Mendesak?</h2>
                <p>Jika mengalami insiden kritis level 1 (gangguan server produksi / kendala presentasi darurat):</p>
                <button type="button" class="hotline-card" data-action="hotline"><span class="hotline-icon"><x-portal.icon name="phone" :size="18" /></span><span><strong>IT Hotline Darurat</strong><small>Ext. 104 / +62 21–5088–0104</small></span></button>
                <button type="button" class="walk-in-card" data-action="location"><x-portal.icon name="pin" :size="18" /><span>Walk-in Tech Bar: Lt. 4<br>Wing B</span><span class="open-badge">Buka</span></button>
            </section>
        </aside>
    </div>
@endsection

@push('scripts')
    <script type="application/json" id="dashboard-preview-data">{!! json_encode(['user' => $user, 'tickets' => $tickets], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush
