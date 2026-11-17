@props(['stat'])
<article class="stat-card stat-{{ $stat['tone'] }}">
    <div class="flex items-center justify-between gap-2">
        <h3>{{ $stat['label'] }}</h3>
        <span class="stat-icon"><x-portal.icon :name="$stat['icon']" :size="17" /></span>
    </div>
    <p class="stat-value">{{ $stat['value'] }}</p>
    <p class="stat-description">@if($stat['tone'] === 'blue')<span class="status-dot" aria-hidden="true"></span>@endif{{ $stat['description'] }}</p>
</article>
