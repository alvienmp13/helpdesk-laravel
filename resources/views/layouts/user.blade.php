<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#005acb">
    <meta name="description" content="Portal layanan IT HelpDesk. Pantau tiket bantuan, temukan solusi, dan lihat perangkat terdaftar Anda.">
    <title>@yield('title', 'Beranda') — HelpDesk Portal Pengguna</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="portal-body">
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    <div class="sidebar-backdrop" data-sidebar-backdrop hidden></div>
    <x-portal.sidebar />

    <div class="portal-workspace">
        <x-portal.topbar :user="$user" />
        <main id="main-content" class="dashboard-content" tabindex="-1">
            @if(session('success'))<div class="portal-feedback portal-feedback-success" role="status">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="portal-feedback portal-feedback-error" role="alert"><strong>Periksa isian berikut:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><p>Jika mengunggah lampiran, pilih ulang berkas sebelum mengirim kembali.</p></div>@endif
            @yield('content')
        </main>
    </div>

    <div class="portal-toast" data-toast role="status" aria-live="polite" hidden></div>

    <dialog class="portal-dialog" data-info-dialog aria-labelledby="dialog-title">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="eyebrow mb-2">HELPDESK · PRATINJAU</p>
                <h2 id="dialog-title" class="text-xl font-semibold" data-dialog-title></h2>
            </div>
            <button type="button" class="icon-button shrink-0" data-dialog-close aria-label="Tutup dialog"><x-portal.icon name="x" /></button>
        </div>
        <div class="dialog-content" data-dialog-content></div>
        <form method="dialog" class="mt-6 flex justify-end">
            <button class="primary-button" type="submit">Mengerti</button>
        </form>
    </dialog>
    @stack('scripts')
</body>
</html>
