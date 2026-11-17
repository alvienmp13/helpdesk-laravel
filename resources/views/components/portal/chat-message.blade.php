@props(['message', 'user'])
<article @class(['ai-chat-message', 'chat-message-own' => $message['role'] === 'user']) data-chat-message="{{ $message['id'] }}" aria-label="{{ $message['role'] === 'user' ? 'Pesan Anda' : 'Jawaban AI demo' }}">
    <span class="chat-message-avatar"><x-portal.icon :name="$message['role'] === 'user' ? 'user' : 'bot'" :size="18" /></span>
    <div class="chat-message-main">
        <div class="chat-message-byline"><span data-chat-author>{{ $message['role'] === 'user' ? $user['name'] : 'HelpDesk Copilot AI' }}</span><time data-chat-time>{{ $message['time'] }}</time>@if($message['role'] === 'assistant')<span class="chat-demo-label">Demo</span><span class="chat-answer-tag" data-answer-tag @if(empty($message['tag'])) hidden @endif>{{ $message['tag'] ?? '' }}</span>@endif</div>
        <div class="ai-chat-bubble">
            <p class="chat-message-text" data-chat-text>{{ $message['text'] }}</p>
            @if($message['role'] === 'assistant')
                <section class="chat-answer-guide" data-chat-guide @if(empty($message['steps'])) hidden @endif><h3><x-portal.icon name="list" :size="16" /><span data-guide-title>{{ $message['guide'] ?? '' }}</span></h3><ol data-chat-steps>@foreach($message['steps'] ?? [] as $step)<x-portal.chat-step :step="$step" :number="$loop->iteration" />@endforeach</ol></section>
                <div class="chat-answer-footer" data-answer-feedback @if($message['welcome'] ?? false) hidden @endif><span>Apakah panduan ini membantu?</span><button type="button" data-chat-feedback="up" aria-label="Panduan membantu" aria-pressed="false"><x-portal.icon name="thumb-up" :size="14" /></button><button type="button" data-chat-feedback="down" aria-label="Panduan belum membantu" aria-pressed="false"><x-portal.icon name="thumb-down" :size="14" /></button><a href="{{ route('user.tickets.create') }}">Buat tiket<x-portal.icon name="arrow-up-right" :size="12" /></a></div>
            @endif
        </div>
    </div>
</article>
