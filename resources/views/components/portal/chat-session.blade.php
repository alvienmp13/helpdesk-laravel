@props(['session', 'selected' => false])
<button type="button" @class(['chat-history-item', 'is-selected' => $selected]) data-chat-session="{{ $session['id'] }}" aria-pressed="{{ $selected ? 'true' : 'false' }}" aria-controls="chat-message-feed" aria-label="Buka percakapan {{ $session['title'] }}" title="{{ $session['title'] }}">
    <span class="chat-session-icon"><x-portal.icon :name="$session['icon'] ?? 'chat'" :size="16" /></span>
    <span class="chat-session-copy"><span class="chat-session-title" data-session-title>{{ $session['title'] }}</span><span class="chat-session-meta"><span data-session-status>{{ $session['status'] }}</span><time data-session-time>{{ $session['time'] }}</time></span></span>
</button>
