@props(['step', 'number' => 1])
<li class="chat-guide-step">
    <label><input type="checkbox" data-chat-step="{{ $number - 1 }}" @checked($step['checked'] ?? false)><span><strong data-step-title>{{ $number }}. {{ $step['title'] }}:</strong><span data-step-body> {{ $step['body'] }}</span></span></label>
    <div class="chat-code-block" @if(empty($step['code'])) hidden @endif><pre><code data-chat-code>{{ $step['code'] ?? '' }}</code></pre><button type="button" data-copy-chat-code aria-label="Salin perintah contoh" title="Salin perintah contoh"><x-portal.icon name="copy" :size="14" /></button></div>
</li>
