@extends('layouts.user')
@section('title', auth()->user()->role === 'user' ? 'Tiket Saya' : 'Penanganan Tiket')
@section('content')
<div class="tickets-page">
    <header class="tickets-page-heading">
        <div><p class="tickets-breadcrumb"><span>Self-Service IT Support</span><span aria-hidden="true">&bull;</span>{{ $user['role'] }}</p><h1>{{ auth()->user()->role === 'user' ? 'Tiket Saya' : 'Penanganan Tiket' }}</h1><p class="tickets-page-description">Pantau status perkembangan dan riwayat laporan bantuan IT.</p></div>
        <div class="tickets-heading-actions">
            <div class="ticket-count-pill"><span class="status-dot"></span><span>Tiket Berjalan<strong>{{ $counts['active'] }} Laporan</strong></span></div>
            <div class="ticket-count-pill count-completed"><span class="status-dot"></span><span>Terselesaikan<strong>{{ $counts['completed'] }} Arsip</strong></span></div>
            @can('create', App\Models\Ticket::class)<a href="{{ route('user.tickets.create') }}" class="sidebar-create tickets-create"><x-portal.icon name="plus-circle" :size="15" /><span>+ Buat Tiket Baru</span></a>@endcan
        </div>
    </header>
    <section class="tickets-toolbar" aria-label="Pencarian dan filter tiket">
        <nav class="ticket-tabs" aria-label="Status tiket">
            @foreach(['all' => 'Semua Tiket', 'active' => 'Sedang Aktif', 'waiting' => 'Menunggu Respon', 'completed' => 'Telah Selesai', 'draft' => 'Draft'] as $key => $label)
                <a href="{{ $listUrl.'?'.http_build_query(array_merge($filters, ['status' => $key, 'page' => 1])) }}" @class(['ticket-tab', 'is-active' => ($filters['status'] ?? 'all') === $key]) @if(($filters['status'] ?? 'all') === $key) aria-current="page" @endif><span>{{ $label }}</span><span class="tab-count">{{ $counts[$key] }}</span></a>
            @endforeach
        </nav>
        <form method="GET" action="{{ $listUrl }}" class="ticket-search-controls" data-ticket-search-form>
            <label class="ticket-search"><x-portal.icon name="search" :size="15" /><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="150" placeholder="Cari nomor atau judul tiket..." aria-label="Cari nomor atau judul tiket" data-ticket-search></label>
            <label class="sr-only" for="status-filter">Status</label><select id="status-filter" name="status" class="ticket-filter-button"><option value="all">Semua status</option>@foreach(['active' => 'Sedang Aktif', 'waiting' => 'Menunggu Respon', 'completed' => 'Telah Selesai'] as $key => $label)<option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>@endforeach @foreach($statuses as $status)<option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
            <label class="sr-only" for="ticket-priority">Prioritas</label><select id="ticket-priority" name="priority" class="ticket-filter-button">@foreach(['all' => 'Semua prioritas', 'low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'urgent' => 'Mendesak'] as $key => $label)<option value="{{ $key }}" @selected(($filters['priority'] ?? 'all') === $key)>{{ $label }}</option>@endforeach</select>
            <button type="submit" class="ticket-filter-button"><x-portal.icon name="search" :size="15" />Cari</button>
        </form>
    </section>
    <div class="tickets-access-notice"><x-portal.icon name="shield" :size="17" /><p><strong>Akses Terverifikasi:</strong> {{ auth()->user()->role === 'user' ? 'Hanya tiket milik Anda yang ditampilkan. Status penanganan dikelola tim IT.' : (auth()->user()->role === 'agent' ? 'Hanya tiket yang ditugaskan kepada Anda yang ditampilkan.' : 'Anda dapat meninjau seluruh tiket dan menugaskan agent.') }}</p></div>
    <div id="ticket-results" class="tickets-workspace">
        <div class="tickets-list-column">
            <section id="personal-ticket-list" class="personal-tickets" aria-labelledby="ticket-list-title">
                <div class="personal-tickets-heading"><h2 id="ticket-list-title"><x-portal.icon name="ticket" :size="17" />Daftar Tiket</h2><p role="status">{{ $pagination->total() }} laporan ditemukan</p></div>
                <div class="ticket-list-items">
                    @forelse($tickets as $ticket)
                        <x-portal.ticket-card :ticket="$ticket" variant="list" :selected="$selectedTicket && $selectedTicket['key'] === $ticket['key']" />
                    @empty
                        <div class="ticket-list-empty"><x-portal.icon name="search" :size="28" /><h3>Belum ada tiket yang cocok</h3><p>Coba kata kunci atau filter lain, atau buat tiket baru.</p><a href="{{ $listUrl }}" class="text-link">Tampilkan semua tiket</a></div>
                    @endforelse
                </div>
                <div class="ticket-pagination">{{ $pagination->links() }}</div>
            </section>
            <div class="tickets-knowledge-hint"><span class="knowledge-hint-icon"><x-portal.icon name="lightbulb" :size="21" /></span><div><h3>Butuh panduan mandiri tanpa menunggu antrean?</h3><p>Pelajari panduan penanganan kendala IT.</p></div><button type="button" data-action="knowledge">Buka Knowledge Base</button></div>
        </div>
        <div id="ticket-detail-area" class="tickets-detail-column" aria-label="Detail tiket terpilih">
            @if($selectedTicket)
                <x-portal.ticket-detail :ticket="$selectedTicket" :selected="true" :agents="$agents" :list-url="$listUrl" />
            @else
                <div class="ticket-detail-empty"><x-portal.icon name="ticket" :size="32" /><h2>Belum ada tiket terpilih</h2><p>Pilih tiket untuk melihat detail laporan.</p></div>
            @endif
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script type="application/json" id="tickets-preview-data">{!! json_encode(['user' => $user, 'tickets' => [], 'listUrl' => $listUrl], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush
