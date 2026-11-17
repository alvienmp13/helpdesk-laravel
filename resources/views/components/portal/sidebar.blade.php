<aside id="portal-sidebar" class="portal-sidebar" aria-label="Navigasi portal pengguna">
    <div class="sidebar-brand">
        <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="flex items-center gap-3" aria-label="HelpDesk Beranda">
            <span class="brand-mark"><x-portal.icon name="headset" :size="25" /></span>
            <span><span class="block text-base font-semibold tracking-tight">HelpDesk</span><span class="block text-[10px] text-slate-600">Self-Service Portal</span></span>
        </a>
        <button type="button" class="icon-button sidebar-close" data-sidebar-close aria-label="Tutup navigasi"><x-portal.icon name="x" /></button>
    </div>

    @if(auth()->user()->role === 'user')
    <a href="{{ route('user.tickets.create') }}" class="sidebar-create" @if(request()->routeIs('user.tickets.create')) aria-current="page" @endif><x-portal.icon name="plus-circle" :size="15" />Buat Tiket Baru</a>

    <p class="nav-label">Menu Pengguna</p>
    <nav class="sidebar-nav" aria-label="Menu utama">
        <a href="{{ route('user.dashboard') }}" @class(['nav-item', 'is-active' => request()->routeIs('user.dashboard')]) @if(request()->routeIs('user.dashboard')) aria-current="page" @endif><x-portal.icon name="grid" :size="17" /><span>Beranda</span></a>
        <a href="{{ route('user.tickets') }}" @class(['nav-item', 'is-active' => request()->routeIs('user.tickets', 'user.tickets.edit', 'tickets.show')]) @if(request()->routeIs('user.tickets', 'user.tickets.edit', 'tickets.show')) aria-current="page" @endif><x-portal.icon name="ticket" :size="17" /><span>Tiket Saya</span></a>
        <a href="{{ route('user.chat') }}" @class(['nav-item', 'is-active' => request()->routeIs('user.chat')]) @if(request()->routeIs('user.chat')) aria-current="page" @endif><x-portal.icon name="bot" :size="17" /><span>Chat dengan AI</span><span class="nav-ai-badge">24/7</span></a>
        <button type="button" class="nav-item" data-action="knowledge"><x-portal.icon name="book" :size="17" /><span>Pusat Bantuan & FAQ</span></button>
    </nav>

    @else
    <p class="nav-label">IT Support</p>
    <nav class="sidebar-nav" aria-label="Menu utama">
        <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="nav-item"><x-portal.icon name="grid" :size="17" /><span>Beranda</span></a>
        <a href="{{ route('support.tickets') }}" class="nav-item is-active"><x-portal.icon name="ticket" :size="17" /><span>Penanganan Tiket</span></a>
    </nav>
    @endif

    <div class="sidebar-bottom">
        <p class="nav-label">Preferensi</p>
        <nav class="sidebar-nav" aria-label="Preferensi akun">
            <button type="button" class="nav-item" data-action="settings"><x-portal.icon name="settings" :size="17" /><span>Pengaturan</span></button>
            <form method="POST" action="{{ route('logout') }}" data-logout-form>@csrf<button type="submit" class="nav-item nav-logout"><x-portal.icon name="logout" :size="17" /><span>Keluar</span></button></form>
        </nav>
    </div>
</aside>
