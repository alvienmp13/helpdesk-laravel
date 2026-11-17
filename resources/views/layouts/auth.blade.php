<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#005bce">
    <title>@yield('title') — HelpDesk</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="portal-body auth-body">
    <a class="skip-link" href="#auth-content">Lewati ke formulir</a>
    <main class="auth-shell">
        <aside class="auth-intro" aria-label="Portal IT HelpDesk">
            <a href="{{ route('home') }}" class="auth-brand"><span class="brand-mark"><x-portal.icon name="headset" :size="26" /></span><span><strong>HelpDesk</strong><small>Self-Service Portal</small></span></a>
            <div class="auth-intro-copy"><span class="auth-eyebrow">IT SUPPORT, MADE SIMPLE</span><h1>Bantuan IT.<br>Lebih dekat,<br>lebih mudah.</h1><p>Satu tempat untuk menyampaikan kendala, memantau tiket, dan menemukan panduan bantuan IT.</p>
                <div class="auth-benefits"><div><x-portal.icon name="ticket" /><span><strong>Pantau bantuan Anda</strong><small>Temukan tiket dan perkembangannya dalam satu portal.</small></span></div><div><x-portal.icon name="chat" /><span><strong>Temukan panduan</strong><small>Akses bantuan mandiri dan percakapan HelpDesk.</small></span></div><div><x-portal.icon name="shield" /><span><strong>Akses sesuai peran</strong><small>Ruang kerja terpisah untuk pengguna, agent, dan admin.</small></span></div></div>
            </div>
            <p class="auth-intro-footer">IT HelpDesk · Employee Support Portal</p>
        </aside>
        <section id="auth-content" class="auth-content" tabindex="-1">
            <div class="auth-form-card">@yield('content')</div>
            <p class="auth-page-footer">HelpDesk · Dukungan untuk setiap hari kerja Anda</p>
        </section>
    </main>
</body>
</html>
