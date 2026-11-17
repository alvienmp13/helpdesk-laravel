@props(['ticket', 'variant' => 'dashboard', 'selected' => false])
@if($variant === 'list')
    <a href="{{ $ticket['url'] }}" @class(['ticket-list-card', 'is-selected' => $selected, 'is-completed' => $ticket['state'] === 'completed']) aria-label="Lihat tiket {{ $ticket['id'] }}: {{ $ticket['title'] }}">
        <span class="list-ticket-icon list-icon-{{ $ticket['tone'] }}"><x-portal.icon :name="$ticket['icon']" :size="18" /></span>
        <span class="list-ticket-content">
            <span class="list-ticket-meta"><span class="list-ticket-id">#{{ $ticket['id'] }}</span><x-portal.badge tone="subtle">{{ $ticket['category'] }}</x-portal.badge>@if($ticket['state'] === 'completed')<span class="list-ticket-date">{{ $ticket['time'] }}</span>@endif</span>
            @if($ticket['state'] !== 'completed')<span class="list-ticket-date block">{{ $ticket['time'] }}</span>@endif
            <span class="list-ticket-title" title="{{ $ticket['title'] }}">{{ $ticket['title'] }}</span>
            <span class="list-ticket-description">{{ $ticket['description'] }}</span>
            <span class="list-ticket-footer"><span class="list-technician"><span class="tiny-avatar avatar-{{ $ticket['tone'] }}">{{ $ticket['initials'] }}</span>Teknisi: {{ $ticket['technician'] }} <span class="technician-team">({{ $ticket['team'] }})</span></span>@if($ticket['state'] === 'completed')<span class="list-resolved"><x-portal.icon name="check-circle" :size="12" />Terselesaikan</span><span class="list-archive-priority">{{ $ticket['priority_label'] }}</span>@endif</span>
            @if($ticket['state'] !== 'completed')<span class="list-priority priority-{{ $ticket['priority'] }}"><x-portal.icon :name="$ticket['priority'] === 'high' ? 'alert' : 'sliders'" :size="13" />{{ $ticket['priority_label'] }}</span>@endif
        </span>
        <span class="list-ticket-side"><x-portal.badge :tone="$ticket['tone']" :dot="$ticket['state'] !== 'completed'">{{ $ticket['status'] }}</x-portal.badge><span class="list-detail-link">{{ $ticket['state'] === 'completed' ? 'Lihat Detail' : 'Lihat Detail & Balas' }}<x-portal.icon name="chevron-right" :size="12" /></span></span>
    </a>
@else
<article id="ticket-{{ $ticket['id'] }}" class="ticket-card" data-ticket-card>
    <div class="ticket-meta">
        <div class="flex flex-wrap items-center gap-2">
            <span class="ticket-number">#{{ $ticket['id'] }}</span>
            <x-portal.badge :tone="$ticket['tone']" :dot="true">{{ $ticket['status'] }}</x-portal.badge>
            <x-portal.badge tone="subtle">{{ $ticket['category'] }}</x-portal.badge>
        </div>
        <span class="ticket-time">{{ $ticket['time'] }}</span>
    </div>
    <h3>{{ $ticket['title'] }}</h3>
    <p class="ticket-description">{{ $ticket['description'] }}</p>
    @isset($ticket['technician'])
        <div class="ticket-assignee">
            <div class="flex items-center gap-2.5">
                <span class="technician-avatar">{{ $ticket['initials'] }}</span>
                <div><div class="flex flex-wrap items-center gap-1.5"><span class="technician-name">{{ $ticket['technician'] }}</span><span class="technician-role">{{ $ticket['technician_role'] }}</span></div><p class="technician-caption">Teknisi Penanggung Jawab</p></div>
            </div>
            <div class="ticket-assignee-actions">
                
                <a class="ticket-detail-button" href="{{ $ticket['url'] }}">Detail Tiket</a>
            </div>
        </div>
    @endisset
    @isset($ticket['note'])
        <div class="ticket-note">
            <x-portal.icon name="alert" :size="19" class="note-icon" />
            <div class="min-w-0 flex-1"><p class="note-title">Catatan dari Tim Admin IT:</p><p class="note-text">“{{ $ticket['note'] }}”</p></div>
            <button type="button" class="ticket-response-button" data-ticket-detail="{{ $ticket['id'] }}">Beri Respon</button>
        </div>
    @endisset
</article>
@endif
