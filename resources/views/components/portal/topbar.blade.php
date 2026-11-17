@props(['user'])
<header class="portal-topbar">
    <button type="button" class="icon-button mobile-menu" data-sidebar-toggle aria-controls="portal-sidebar" aria-expanded="false" aria-label="Buka navigasi"><x-portal.icon name="menu" /></button>
    <form class="topbar-search" role="search" method="GET" action="{{ route(auth()->user()->role === 'user' ? 'user.tickets' : 'support.tickets') }}" data-search-form>
        <x-portal.icon name="search" :size="16" />
        <input type="search" name="q" aria-label="Cari solusi atau tiket" placeholder="Cari solusi, panduan mandiri, atau tiket saya..." autocomplete="off" data-search-input>
    </form>
    <div class="topbar-actions">
        <a href="{{ route(auth()->user()->dashboardRoute()) }}#urgent-help" class="icon-button help-button" aria-label="Bantuan IT"><x-portal.icon name="help" :size="18" /></a>
        <div class="relative">
            <button type="button" class="icon-button notification-button" data-notification-toggle aria-label="Notifikasi" aria-expanded="false" aria-controls="notification-panel"><x-portal.icon name="bell" :size="18" /></button>
            <section id="notification-panel" class="notification-panel" aria-label="Notifikasi terbaru" hidden>
                <h2 class="mb-3 text-sm font-semibold">Notifikasi terbaru</h2>
                <p class="ticket-description">Lihat status terbaru pada daftar tiket Anda.</p>
                <a href="{{ route(auth()->user()->role === 'user' ? 'user.tickets' : 'support.tickets') }}">Buka daftar tiket</a>
            </section>
        </div>
        <div class="topbar-divider"></div>
        <button type="button" class="profile-button" data-action="profile" aria-label="Profil {{ $user['name'] }}">
            <span class="profile-avatar"><x-portal.icon name="user" :size="19" /></span>
            <span class="profile-copy"><strong>{{ $user['name'] }}</strong><span>{{ $user['role'] }}</span></span>
        </button>
    </div>
</header>
