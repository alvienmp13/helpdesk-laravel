@props(['service'])
<article class="service-card service-{{ $service['tone'] }}">
    <div class="mb-4 flex items-start justify-between gap-2">
        <span class="service-icon"><x-portal.icon :name="$service['icon']" :size="25" /></span>
        <span class="service-badge">{{ $service['badge'] }}</span>
    </div>
    <h2>{{ $service['title'] }}</h2>
    <p>{{ $service['description'] }}</p>
    @if($service['target'] === 'create-ticket')
        <a href="{{ route('user.tickets.create') }}" class="service-action">{{ $service['action'] }}<x-portal.icon :name="$service['action_icon']" :size="15" /></a>
    @elseif($service['target'] === 'ai')
        <a href="{{ route('user.chat') }}" class="service-action">{{ $service['action'] }}<x-portal.icon :name="$service['action_icon']" :size="15" /></a>
    @else
        <button type="button" class="service-action" data-action="{{ $service['target'] }}">{{ $service['action'] }}<x-portal.icon :name="$service['action_icon']" :size="15" /></button>
    @endif
</article>
