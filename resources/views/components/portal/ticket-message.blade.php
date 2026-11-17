@props(['message'])
<div @class(['ticket-message', 'message-own' => $message['own']])>
    <p class="message-byline"><span data-message-author>{{ $message['author'] }}</span><span aria-hidden="true"> · </span><time data-message-time>{{ $message['time'] }}</time></p>
    <div class="message-bubble"><p data-message-body>{{ $message['body'] }}</p>@foreach(($message['attachments'] ?? []) as $attachment)<div class="message-attachment"><a href="{{ route('tickets.attachments.download', $attachment) }}">Unduh: {{ $attachment->original_name }}</a></div>@endforeach</div>
</div>
