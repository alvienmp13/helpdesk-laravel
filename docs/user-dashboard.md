# Beranda Portal Pengguna

Implementasi halaman `/user/dashboard` berdasarkan `design/references/user-beranda.png`. Area aplikasi pada screenshot menjadi acuan; judul dan latar kanvas gelap di luar aplikasi tidak menjadi bagian halaman.

## Menjalankan

Mulai tahap 5, masuk melalui `/login` dengan role `user` sebelum membuka portal. Daftar melalui `/register` jika belum mempunyai akun. Lihat [panduan autentikasi](authentication.md).

Dari folder `helpdesk-app`, jalankan:

```sh
npm run build
php artisan serve
```

Buka **http://127.0.0.1:8000/user/dashboard**. Untuk mengedit frontend dengan hot reload, jalankan `npm run dev` di terminal kedua. Jika server Laravel menggunakan port lain, sesuaikan URL tersebut.

Dependensi proyek yang digunakan: Laravel 13, Blade, Tailwind CSS 4, dan Vite 7. Tidak ada package frontend tambahan. Font Inter disimpan lokal, disertai lisensi OFL, dan diproses melalui Vite.

## Susunan file

| File | Peran |
| --- | --- |
| `routes/web.php` | Route GET `/user/dashboard`, bernama `user.dashboard`. Route `/` tetap tersedia. |
| `app/Http/Controllers/User/DashboardController.php` | Menyediakan seluruh data dummy pengguna, layanan, statistik, tiket, perangkat, dan prompt. Tidak menjalankan query database. |
| `resources/views/layouts/user.blade.php` | Layout reusable, pemuatan Vite, sidebar, topbar, dialog, serta notifikasi singkat. |
| `resources/views/user/dashboard.blade.php` | Susunan bagian Beranda sesuai screenshot. |
| `resources/views/components/portal/` | Komponen `sidebar`, `topbar`, `service-card`, `stat-card`, `ticket-card`, `badge`, dan ikon SVG `icon`. |
| `resources/css/app.css` | Tailwind, token warna, tipografi, ukuran, dan aturan responsif portal. |
| `resources/js/app.js` | Interaksi lokal menu mobile, pencarian, notifikasi, detail tiket, dan pratinjau jawaban AI. |
| `resources/fonts/` | Font Inter variable beserta `Inter-LICENSE.txt`. |
| `public/build/` | Aset produksi dan manifest yang dihasilkan `npm run build`. |

## Perilaku pratinjau

- Desktop: sidebar 224 px, tiga kartu layanan, empat statistik, kolom tiket dan kolom bantuan. Sidebar menjadi 208 px pada desktop yang lebih sempit.
- Lebar di bawah 1024 px: sidebar menjadi drawer dengan backdrop, dukungan tombol Escape, dan pengelolaan fokus keyboard.
- Lebar di bawah 768 px: konten menjadi satu kolom dan statistik menjadi dua kolom.
- Pencarian dijalankan dengan Enter dan mencari tiket/panduan dummy. Contoh: `Figma`, `MacBook`, atau `VPN`.
- `Tiket Saya` dan `Lihat Semua Riwayat` menuju `/user/tickets`. Detail ringkas di Beranda, notifikasi, FAQ, dan profil tetap menggunakan data contoh.
- Buat Tiket Baru membuka `/user/tickets/create`, dengan formulir dan respons simulasi yang dijelaskan di `docs/user-ticket-create.md`. Pengaturan dan logout tetap berupa pratinjau. Daftar, detail, dan balasan lokal pada tahap kedua dijelaskan di `docs/user-tickets.md`.
- Panel AI menampilkan jawaban contoh lokal, diberi label data dummy setelah pertanyaan dikirim. Tidak ada API atau integrasi n8n.
- Menu Chat dengan AI dan tombol layanan AI menuju `/user/chat`. Halaman percakapan lengkap menggunakan respons simulasi dan riwayat lokal selama halaman terbuka; lihat `docs/user-chat.md`.
- Statistik, status operasional, jam kerja, perangkat, serta kontak IT adalah data dummy. Tidak ada data pratinjau yang disimpan.

Layout tahap pertama dipertahankan. Mulai tahap 5, identitas pengguna berasal dari sesi login dan halaman dilindungi middleware `auth` serta `role:user`. Data tiket dan perangkat tetap contoh; kredensial `.env` serta konfigurasi database tidak diubah.

## Pemeriksaan

```sh
npm run build
php artisan route:list --path=user/dashboard
php artisan view:cache
php artisan test
```

Build Vite, kompilasi Blade, dan dua tes bawaan proyek lulus. Pemeriksaan browser meliputi render HTTP 200, pemuatan font lokal, interaksi pratinjau, menu mobile, serta ukuran viewport 320–1440 px. Screenshot hasil pemeriksaan hanya menjadi artefak lokal, bukan aset halaman.
