# Chat dengan AI

Halaman **`/user/chat`**, route `user.chat`, mengikuti area aplikasi dalam `design/references/user-chat-ai.png`. Kanvas gelap dan bingkai ungu di luar aplikasi pada screenshot bukan bagian halaman. Sidebar, topbar, font Inter lokal, warna, badge, ikon, dialog, dan toast memakai komponen portal sebelumnya.

## Menjalankan

Mulai tahap 5, masuk melalui `/login` dengan role `user` terlebih dahulu. Nama akun berasal dari sesi autentikasi; percakapan dan respons AI tetap demo. Lihat [panduan autentikasi](authentication.md).

Buka **http://127.0.0.1:8000/user/chat** jika Laravel sudah berjalan. Menu **Chat dengan AI** pada sidebar dan tombol layanan AI di Beranda menuju halaman ini.

Jika server belum berjalan, dari direktori `C:\projectss\helpdesk-app`:

```powershell
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Biarkan terminal Laravel tetap berjalan. Untuk hot reload selama mengedit, jalankan `npm run dev` pada terminal kedua. Buka aplikasi di **port 8000**; Vite hanya menyediakan CSS/JavaScript. Untuk memakai aset produksi, hentikan Vite melalui Ctrl+C, jalankan `npm run build`, dan biarkan Laravel berjalan. Jika `php` belum ada di PATH, gunakan `& 'C:\xampp83\php\php.exe' artisan serve --host=127.0.0.1 --port=8000`.

## Cara mencoba

1. Buka halaman. Sesi awal menampilkan percakapan contoh VPN, dengan pesan pengguna di kanan, jawaban AI di kiri, serta panduan berisi checklist dan blok perintah contoh.
2. Pilih sesi reset password, akses software, atau printer. Cari sesi melalui kolom riwayat atau pencarian pada topbar. Pencarian mencakup judul dan teks percakapan; tersedia reset ketika tidak ada hasil.
3. Tekan **Percakapan Baru** atau **Mulai Sesi Baru**. Sesi baru menampilkan sapaan; judulnya berubah mengikuti pesan pertama.
4. Tulis pesan dan tekan Enter atau tombol kirim. Shift+Enter menambahkan baris baru. Pesan kosong dan pesan lebih dari 2.000 karakter ditolak. Tombol kirim dinonaktifkan selama balasan untuk sesi tersebut diproses.
5. Indikator loading muncul sekitar 1,1 detik sebelum respons simulasi. Coba topik VPN, Wi-Fi, Outlook, password/SSO, lisensi software, printer, atau perangkat. Pertanyaan lain mendapatkan panduan umum demo.
6. Klik saran pertanyaan atau kategori bantuan populer untuk mengirim contoh pertanyaan. Draft yang sudah diketik tetap tersedia jika saran diklik.
7. Kirim pesan lalu segera pindah atau buat sesi lain. Balasan tetap masuk ke sesi asal. Draft teks, checklist, penilaian, dan posisi baca dipertahankan per sesi selama halaman ini terbuka.
8. Coba checklist, tombol salin perintah, serta penilaian membantu/belum membantu. Tombol salin menampilkan opsi salin manual jika browser menolak Clipboard API. Perintah hanya teks contoh dan tidak dijalankan aplikasi.
9. Ikon lampiran, gambar, dan mikrofon membuka informasi demo. Upload atau perekaman chat belum tersedia. Tautan **Buat tiket** membuka formulir yang sudah dibuat pada tahap sebelumnya.
10. Pada smartphone, gunakan tombol **Riwayat** untuk membuka atau menutup daftar sesi. Sidebar utama tetap menggunakan drawer portal. Area pesan dapat digulir secara terpisah, dengan saran dan input di bagian bawah kartu chat.
11. Muat ulang halaman untuk kembali ke empat sesi contoh. Buka kembali Beranda, Tiket Saya, dan Buat Tiket Baru melalui navigasi untuk memeriksa halaman sebelumnya.

## Batas demo

Semua jawaban dibuat dari data contoh lokal, ditandai **Demo** pada header dan pesan AI. Status Online 24/7, riwayat, tanggal, dan nomor tiket dalam sesi awal adalah contoh tampilan. Tidak ada koneksi API AI, n8n, akses perangkat, akses akun, atau query data tiket oleh controller chat.

Pesan, draft chat, checklist, serta penilaian hanya berada di memori halaman browser. Tidak ada penyimpanan chat ke `localStorage`, `sessionStorage`, atau database; data kembali ke contoh awal setelah reload. Pengiriman pesan tidak melakukan POST atau request ke layanan AI. Draft pada halaman Buat Tiket Baru tetap menggunakan mekanisme lokal yang sudah ada pada tahap sebelumnya.

## File

| File | Perubahan |
| --- | --- |
| `app/Http/Controllers/User/ChatController.php` | Baru: data empat sesi contoh, panduan simulasi, kategori, dan rekomendasi. |
| `resources/views/user/chat.blade.php` | Baru: header chat, panel riwayat, area pesan, loading, rekomendasi, input, dan template DOM. |
| `resources/views/components/portal/chat-session.blade.php` | Baru: tombol sesi dengan status dan waktu. |
| `resources/views/components/portal/chat-message.blade.php` | Baru: pesan pengguna/AI, label demo, panduan, penilaian, dan tautan tiket. |
| `resources/views/components/portal/chat-step.blade.php` | Baru: langkah checklist dan blok perintah yang dapat disalin. |
| `resources/css/chat.css` | Baru: ukuran, warna, susunan panel, dan aturan responsif sesuai referensi. |
| `resources/js/chat.js` | Baru: interaksi sesi, pencarian, draft per sesi, respons tertunda, validasi, rekomendasi, checklist, dan penilaian lokal. |
| `routes/web.php` | Menambahkan GET `/user/chat`. |
| `resources/css/app.css` | Mengimpor stylesheet chat melalui Vite. |
| `resources/js/app.js` | Menginisialisasi chat dan menghubungkan pencarian topbar; menghapus aksi navigasi AI lama yang sudah digantikan tautan route. Panel AI ringkas Beranda tetap berfungsi. |
| `resources/views/components/portal/sidebar.blade.php` | Tautan chat menuju route baru dengan penanda halaman aktif. |
| `resources/views/components/portal/service-card.blade.php` | Tombol layanan AI Beranda menuju halaman chat. |
| `resources/views/components/portal/icon.blade.php` | Menambahkan ikon chat ke komponen SVG yang sudah tersedia. |
| `docs/user-dashboard.md`, `docs/user-chat.md` | Petunjuk navigasi dan pengujian. |
| `public/build/` | Aset produksi hasil build Vite. |

Tidak ada dependensi baru untuk chat. Autentikasi dan migration role ditambahkan terpisah pada tahap 5, tanpa mengubah kredensial `.env` atau konfigurasi database. Percakapan tetap berada di memori halaman dan tidak menjadi data chat backend.

## Pemeriksaan

```powershell
node --check resources/js/chat.js
node --check resources/js/app.js
php -l app/Http/Controllers/User/ChatController.php
php artisan route:list --path=user
php artisan view:cache
php artisan test
npm run build
```

Pemeriksaan browser menggunakan aset build produksi, viewport 320–1440 px, pergantian sesi ketika balasan masih diproses, draft terpisah, Enter/Shift+Enter dan input IME, validasi, keamanan teks pesan, pencarian, rekomendasi, checklist, penilaian, penyalinan perintah, dialog demo, navigasi mobile, serta reload. Halaman Beranda, Tiket Saya, dan Buat Tiket Baru juga diperiksa kembali. Skrip dan screenshot pemeriksaan lokal berada di luar aplikasi, pada `C:\projectss\.qa-helpdesk`.
