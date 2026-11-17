@props(['tone' => 'blue', 'dot' => false])
<span {{ $attributes->class(['portal-badge', 'badge-'.$tone]) }}>
    @if($dot)<span class="status-dot" aria-hidden="true"></span>@endif
    {{ $slot }}
</span>
