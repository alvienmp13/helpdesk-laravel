@props(['name', 'size' => 20])
<svg {{ $attributes->merge(['class' => 'portal-icon']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('headset')
            <path d="M4 14v-3a8 8 0 0 1 16 0v6a3 3 0 0 1-3 3h-3"/><rect x="3" y="11" width="4" height="7" rx="2"/><rect x="17" y="11" width="4" height="7" rx="2"/><path d="M9 8c1.6-1.3 4.4-1.3 6 0"/><path d="M10 20h4"/>
            @break
        @case('grid')
            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
            @break
        @case('plus-circle')
            <circle cx="12" cy="12" r="9"/><path d="M8 12h8m-4-4v8"/>
            @break
        @case('ticket-plus')
            <path d="M20 8a9 9 0 1 0-8 13M8 12l3 3 7-9m0 9v6m-3-3h6"/><path d="M14 3a9 9 0 0 1 4 2"/>
            @break
        @case('ticket')
            <path d="M4 5h16v5a2 2 0 0 0 0 4v5H4v-5a2 2 0 0 0 0-4zM14 5v2m0 3v4m0 3v2"/>
            @break
        @case('bot')
            <rect x="4" y="7" width="16" height="13" rx="2"/><path d="M12 3v4M10 3h4M1 12h3m16 0h3M9 16h6"/><circle cx="9" cy="12" r=".8"/><circle cx="15" cy="12" r=".8"/>
            @break
        @case('book')
            <path d="M12 5c-3-2-7-2-10-1v15c3-1 7-1 10 1 3-2 7-2 10-1V4c-3-1-7-1-10 1v15M5 7h4m-4 4h4m6-4h4m-4 4h4"/>
            @break
        @case('chat')
            <path d="M4 4h13a2 2 0 0 1 2 2v10H8l-5 4V5a1 1 0 0 1 1-1ZM7 8h8m-8 4h5"/><path d="M19 8h2v13l-4-3h-5"/>
            @break
        @case('search')
            <circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>
            @break
        @case('help')
            <circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 4 2c-1 .7-1.5 1-1.5 2.5M12 17h.01"/>
            @break
        @case('bell')
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>
            @break
        @case('user')
            <circle cx="12" cy="7" r="3"/><path d="M6 20v-3a6 6 0 0 1 12 0v3M9 16h6m-3-3v6"/>
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
            @break
        @case('clipboard-clock')
            <path d="M12 21H5a2 2 0 0 1-2-2V5h4m10 0h3v6M8 9h6m-6 4h2"/><rect x="7" y="2" width="9" height="5" rx="1"/><circle cx="17" cy="17" r="4"/><path d="M17 15v2h2"/>
            @break
        @case('check-circle')
            <circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>
            @break
        @case('arrow-right')
            <path d="M4 12h16m-6-6 6 6-6 6"/>
            @break
        @case('arrow-up-right')
            <path d="M6 18 18 6M7 6h11v11"/>
            @break
        @case('chevron-right')
            <path d="m9 5 7 7-7 7"/>
            @break
        @case('bolt')
            <path d="m13 2-9 12h7l-1 8 10-13h-8z"/>
            @break
        @case('send')
            <path d="m3 3 19 9-19 9 3-9-3-9Zm3 9h16"/>
            @break
        @case('link')
            <path d="m10 13 4-4m-5 7-1 1a4 4 0 0 1-6-6l4-4a4 4 0 0 1 6 0m0 10a4 4 0 0 0 6 0l4-4a4 4 0 0 0-6-6l-1 1" transform="translate(1 -1) scale(.92)"/>
            @break
        @case('key')
            <circle cx="8" cy="8" r="5"/><path d="m12 12 9 9m-4-4 3-3m-6 0 3-3M6 6h.01"/>
            @break
        @case('puzzle')
            <path d="M9 3H4v6a3 3 0 1 1 0 6v6h6a3 3 0 1 1 6 0h5v-7a3 3 0 1 0 0-6V3h-6a3 3 0 1 1-6 0Z"/>
            @break
        @case('phone')
            <path d="m5 3 4 4-2 3c2 3 4 5 7 6l3-2 4 4-2 3C10 22 2 14 2 5z"/>
            @break
        @case('pin')
            <path d="M19 9c0 5-7 12-7 12S5 14 5 9a7 7 0 0 1 14 0Z"/><circle cx="12" cy="9" r="2.5"/>
            @break
        @case('laptop')
            <path d="M4 4h16v13H4zM2 20h20l-2-3H4zM10 20h4"/>
            @break
        @case('shield')
            <path d="m12 2 8 3v6c0 5-8 11-8 11S4 16 4 11V5zM12 2v20M4 5l8 4 8-4"/><path d="M12 9v13c6-4 8-7 8-11V5z" fill="currentColor" stroke="none"/>
            @break
        @case('settings')
            <path d="m9 3-.6 3-2 .9L3.5 6l-2 3.5 2.3 2v2l-2.3 2 2 3.5 2.9-.9 2 .9.6 3h4l.6-3 2-.9 2.9.9 2-3.5-2.3-2v-2l2.3-2-2-3.5-2.9.9-2-.9-.6-3z" transform="translate(1 -1)"/><circle cx="12" cy="12" r="3"/>
            @break
        @case('logout')
            <path d="M10 4H4v16h6m-2-8h13m-4-4 4 4-4 4"/>
            @break
        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16"/>
            @break
        @case('x')
            <path d="m6 6 12 12M6 18 18 6"/>
            @break
        @case('monitor')
            <rect x="3" y="3" width="18" height="13" rx="1.5"/><path d="M8 21h8m-4-5v5"/>
            @break
        @case('printer')
            <path d="M6 8V3h12v5M6 17H3V8h18v9h-3M6 14h12v7H6zM17 11h1"/>
            @break
        @case('sliders')
            <path d="M3 6h3m4 0h11M3 12h10m4 0h4M3 18h3m4 0h11M6 3v6m7 0v6M6 15v6"/>
            @break
        @case('lock')
            <rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V6a4 4 0 0 1 8 0v4m-4 5v2"/>
            @break
        @case('check')
            <path d="m5 12 4 4L19 6"/>
            @break
        @case('info')
            <circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10h.01"/>
            @break
        @case('lightbulb')
            <path d="M8 15a7 7 0 1 1 8 0l-1 3H9zM9 21h6m-5 2h4"/>
            @break
        @case('file')
            <path d="M14 2H4v20h16V8l-6-6Zm0 0v6h6M8 13h8m-8 4h5"/>
            @break
        @case('arrow-left')
            <path d="M20 12H4m6-6-6 6 6 6"/>
            @break
        @case('apps')
            <path d="M5 5h.01M12 5h.01M19 5h.01M5 12h.01M12 12h.01M19 12h.01M5 19h.01M12 19h.01M19 19h.01" stroke-width="3"/>
            @break
        @case('network')
            <rect x="3" y="4" width="7" height="6" rx="1"/><rect x="14" y="4" width="7" height="6" rx="1"/><path d="M6.5 10v5h11v-5M12 15v5m-3 0h6"/>
            @break
        @case('user-key')
            <circle cx="9" cy="6" r="3"/><path d="M3 18v-2a6 6 0 0 1 9-5"/><circle cx="16" cy="15" r="3"/><path d="m18 17 3 3m-1-1 1-1"/>
            @break
        @case('mail-check')
            <path d="M21 11V4H3v16h8M3 5l9 7 9-7m-7 13 3 3 5-6"/>
            @break
        @case('cloud-upload')
            <path d="M7 18H5a4 4 0 0 1-.4-8A7 7 0 0 1 18 8a5 5 0 0 1 1 10h-2M12 21V11m-4 4 4-4 4 4"/>
            @break
        @case('image')
            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8" cy="8" r="1.5"/><path d="m3 17 5-5 4 4 4-6 5 6"/>
            @break
        @case('building')
            <path d="M3 21V3h12v18m0-12h6v12M1 21h22M7 7h1m3 0h1M7 11h1m3 0h1M7 15h1m3 0h1m-4 6v-3h4v3m7-8h1m-1 4h1"/>
            @break
        @case('edit')
            <path d="M4 4h8M4 9h6m-6 5h3m7 6 7-7-4-4-7 7-1 5zM15 11l4 4"/>
            @break
        @case('history')
            <path d="M3 11a9 9 0 1 1 2 7M3 4v7h7m2-4v5l4 2"/>
            @break
        @case('list')
            <path d="M9 6h12M9 12h12M9 18h12M3 6h.01M3 12h.01M3 18h.01"/>
            @break
        @case('list-ordered')
            <path d="M10 6h11m-11 6h11m-11 6h11M3 3h1v6m-1 4c3-2 4 1 1 3l-1 2h3"/>
            @break
        @case('chevron-down')
            <path d="m5 9 7 7 7-7"/>
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14"/>
            @break
        @case('sparkles')
            <path d="m12 3 2.5 6.5L21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5L12 3ZM4 2v4M2 4h4m14 14v4m-2-2h4"/>
            @break
        @case('copy')
            <rect x="8" y="7" width="12" height="15" rx="1.5"/><path d="M16 7V2H3v16h5"/>
            @break
        @case('paperclip')
            <path d="m9 12 6-6a3 3 0 0 1 4 4L9 20a5 5 0 0 1-7-7L13 2m-7 13 9-9" transform="translate(2 0) scale(.9)"/>
            @break
        @case('mic')
            <rect x="9" y="2" width="6" height="13" rx="3"/><path d="M5 10v2a7 7 0 0 0 14 0v-2m-7 9v3m-4 0h8"/>
            @break
        @case('thumb-up')
            <path d="M7 10v11H3V10zm0 0 6-8c3 0 2 5 1 7h6a2 2 0 0 1 2 2l-2 8a2 2 0 0 1-2 2H7"/>
            @break
        @case('thumb-down')
            <path d="M7 14V3H3v11zm0 0 6 8c3 0 2-5 1-7h6a2 2 0 0 0 2-2l-2-8a2 2 0 0 0-2-2H7"/>
            @break
        @case('alert')
            <path d="M12 4v10m0 5h.01" stroke-width="2.5"/>
            @break
    @endswitch
</svg>
