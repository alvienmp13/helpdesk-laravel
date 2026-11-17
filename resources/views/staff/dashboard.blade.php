<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Dashboard {{ ucfirst($role) }} — HelpDesk</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="portal-body staff-body">
    <header class="staff-topbar"><a href="{{ route('dashboard') }}" class="auth-brand"><span class="brand-mark"><x-portal.icon name="headset" :size="26" /></span><span><strong>HelpDesk</strong><small>{{ $role === 'admin' ? 'Admin Workspace' : 'Agent Workspace' }}</small></span></a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="staff-logout"><x-portal.icon name="logout" :size="17" />Keluar</button></form></header>
    <main class="staff-content"><x-portal.badge tone="blue">{{ $role === 'admin' ? 'Administrator' : 'IT Support Agent' }}</x-portal.badge><h1>Dashboard {{ ucfirst($role) }}</h1><p>Selamat datang, <strong>{{ auth()->user()->name }}</strong>.</p><section class="staff-placeholder"><span class="auth-form-icon"><x-portal.icon :name="$role === 'admin' ? 'shield' : 'headset'" :size="28" /></span><h2>Ruang kerja {{ $role === 'admin' ? 'Admin' : 'Agent' }}</h2><p>Akun Anda telah masuk dengan akses {{ $role }}. Halaman ini adalah placeholder; fitur dan desain lengkap akan dikembangkan pada tahap berikutnya.</p><p><a class="primary-button" href="{{ route('support.tickets') }}">Buka Penanganan Tiket</a></p><div class="staff-account"><span>{{ auth()->user()->email }}</span><span>Role: {{ $role }}</span></div></section></main>
</body>
</html>
