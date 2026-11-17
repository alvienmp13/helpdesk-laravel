@props(['ticket', 'selected' => true, 'agents' => [], 'listUrl'])
@php($model = $ticket['model'])
<section id="detail-{{ $ticket['id'] }}" class="ticket-detail-view" aria-labelledby="detail-title-{{ $ticket['id'] }}">
    <a href="{{ $listUrl }}" class="tickets-back-link"><x-portal.icon name="arrow-left" :size="16" />Kembali ke daftar tiket</a>
    <div class="ticket-detail-card">
        <div class="ticket-detail-summary">
            <div class="detail-status-row"><span class="detail-ticket-id">#{{ $ticket['id'] }}</span><x-portal.badge :tone="$ticket['tone']" :dot="true">{{ $ticket['status'] }}</x-portal.badge><span class="managed-status"><x-portal.icon name="lock" :size="13" /><span>Status Dikelola<br>Admin IT</span></span></div>
            <h2 id="detail-title-{{ $ticket['id'] }}" tabindex="-1">{{ $ticket['title'] }}</h2>
            <dl class="detail-metadata"><div><dt>Kategori Permasalahan</dt><dd>{{ $ticket['detail_category'] }}</dd></div><div><dt>Waktu Dibuat</dt><dd>{{ $ticket['submitted'] }}</dd></div><div><dt>Departemen / Lokasi</dt><dd>{{ $ticket['department'] ?? '-' }} / {{ $ticket['location'] ?? '-' }}</dd></div><div><dt>Prioritas</dt><dd>{{ $ticket['priority_label'] }}</dd></div></dl>
            <p class="ticket-description ticket-body-text">{{ $ticket['description'] }}</p>
            @foreach($ticket['attachments'] as $attachment)<p class="message-attachment"><a href="{{ route('tickets.attachments.download', $attachment) }}">Unduh: {{ $attachment->original_name }}</a></p>@endforeach
            @can('update', $model)
                <div class="ticket-manage-actions">
                    <a href="{{ route('user.tickets.edit', $model) }}" class="ticket-detail-button">Edit Draft</a>
                    <form method="POST" action="{{ route('user.tickets.submit', $model) }}">@csrf<button class="send-reply-button" type="submit">Kirim Draft</button></form>
                    <form method="POST" action="{{ route('user.tickets.destroy', $model) }}" data-delete-draft-form>@csrf @method('DELETE')<button class="ticket-filter-button" type="submit">Hapus Draft</button></form>
                </div>
            @endcan
        </div>
        <div class="detail-technician"><span class="technician-avatar">{{ $ticket['initials'] }}</span><div class="min-w-0 flex-1"><p class="detail-technician-name">{{ $ticket['technician'] }}</p><p class="detail-technician-role">IT Support</p></div></div>
        @can('assign', $model)
            <form method="POST" action="{{ route('tickets.assign', $model) }}" class="ticket-management-form">@csrf @method('PATCH')
                <label for="assigned-to">Agent Penanggung Jawab</label><select id="assigned-to" name="assigned_to"><option value="">Belum ditugaskan</option>@foreach($agents as $agent)<option value="{{ $agent->id }}" @selected($model->assigned_to === $agent->id)>{{ $agent->name }}</option>@endforeach</select><button type="submit" class="ticket-detail-button">Simpan Penugasan</button>
            </form>
        @endcan
        @can('changeStatus', $model)
            <form method="POST" action="{{ route('tickets.status', $model) }}" class="ticket-management-form">@csrf @method('PATCH')
                <label for="next-status">Status Berikutnya</label><select id="next-status" name="status" required>@foreach($model->status->next() as $status)<option value="{{ $status->value }}">{{ $status->label() }}</option>@endforeach</select>
                <label for="status-note">Catatan Penanganan</label><textarea id="status-note" name="note" rows="2" maxlength="2000">{{ old('note') }}</textarea><button type="submit" class="ticket-detail-button">Perbarui Status</button>
            </form>
        @endcan
        <section class="ticket-timeline" aria-labelledby="timeline-title-{{ $ticket['id'] }}">
            <div class="detail-section-heading"><h3 id="timeline-title-{{ $ticket['id'] }}">Alur Penanganan Laporan</h3><span>{{ count($ticket['timeline']) }} perubahan</span></div>
            <ol>@foreach($ticket['timeline'] as $step)<li class="timeline-step step-{{ $step['state'] }}"><span class="timeline-marker"><x-portal.icon name="check" :size="10" /></span><div><h4>{{ $step['title'] }}</h4><p>{{ $step['detail'] }}</p></div></li>@endforeach</ol>
        </section>
        <section class="ticket-conversation" aria-labelledby="conversation-title-{{ $ticket['id'] }}">
            <div class="detail-section-heading conversation-heading"><h3 id="conversation-title-{{ $ticket['id'] }}"><x-portal.icon name="chat" :size="15" />Percakapan Tiket</h3></div>
            <div class="conversation-messages" role="log" aria-label="Percakapan tiket" tabindex="0">@forelse($ticket['messages'] as $message)<x-portal.ticket-message :message="$message" />@empty<p class="ticket-description">Belum ada pesan.</p>@endforelse</div>
        </section>
        @can('message', $model)
            <form class="ticket-reply-form" method="POST" action="{{ route('tickets.messages.store', $model) }}" enctype="multipart/form-data">@csrf
                <label for="reply-{{ $model->id }}" class="sr-only">Pesan tiket</label><textarea id="reply-{{ $model->id }}" name="body" rows="2" maxlength="2000" placeholder="Tulis pesan untuk tiket ini...">{{ old('body') }}</textarea>
                <div class="reply-actions"><div><label class="attach-button" for="reply-attachments"><x-portal.icon name="file" :size="17" />Lampirkan Bukti</label><input type="file" id="reply-attachments" name="attachments[]" accept=".png,.jpg,.jpeg,.pdf,.log" multiple class="ticket-reply-files"><span class="attachment-guidance">PNG, JPG, PDF, LOG; maks. 5 MB/file, 5 file.</span></div><button type="submit" class="send-reply-button">Kirim Pesan<x-portal.icon name="send" :size="16" /></button></div>
            </form>
        @else
            <div class="ticket-archived-note"><x-portal.icon name="info" :size="19" /><span>{{ $model->status === App\Enums\TicketStatus::Draft ? 'Kirim draft untuk memulai penanganan dan percakapan.' : 'Tiket ditutup. Percakapan tersimpan sebagai arsip.' }}</span></div>
        @endcan
    </div>
</section>
