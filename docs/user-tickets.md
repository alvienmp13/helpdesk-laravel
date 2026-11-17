# Tiket Saya

Halaman `/user/tickets` (route `user.tickets`) mengikuti area aplikasi pada `design/references/user-tiket.png`. Bingkai ungu dan kanvas gelap di luar aplikasi pada referensi tidak menjadi bagian halaman.

## Melihat hasil

Jika server Laravel sudah berjalan, langsung buka **http://127.0.0.1:8000/user/tickets**. Menu sidebar **Beranda** dan **Tiket Saya** dapat dipakai berpindah halaman; menu aktif menyesuaikan route.

Tombol **Buat Tiket Baru** kini membuka `/user/tickets/create`. Formulir dan respons simulasi tahap ketiga dijelaskan di `docs/user-ticket-create.md`.

Untuk menjalankan dari awal, buka terminal di `helpdesk-app`:

```sh
php artisan serve --host=127.0.0.1 --port=8000
```

Biarkan server Laravel berjalan. Untuk hot reload, jalankan `npm run dev` di terminal kedua. Vite menyediakan aset frontend, sedangkan halaman Laravel tetap dibuka di port 8000. `npm run dev` saja tidak menjalankan Laravel.

Untuk aset produksi, hentikan Vite lalu jalankan `npm run build`; tetap jalankan server Laravel. Tidak perlu menjalankan migration atau mengubah `.env`.

## Perubahan file

| File | Perubahan |
| --- | --- |
| `app/Http/Controllers/User/TicketController.php` | Baru: empat tiket dummy, jumlah tab, teknisi, prioritas, timeline, dan percakapan awal. Tidak mengakses database. |
| `resources/views/user/tickets.blade.php` | Baru: judul, ringkasan, tab, pencarian, filter prioritas, pemberitahuan akses, daftar dan panel detail. Memakai `layouts.user` yang sudah ada. |
| `resources/views/components/portal/ticket-card.blade.php` | Menambahkan varian `list` ke komponen kartu yang ada. Varian default untuk Beranda tetap dipertahankan. |
| `resources/views/components/portal/ticket-detail.blade.php` | Baru: metadata, teknisi, timeline, percakapan, form balasan, dan informasi penyelesaian. |
| `resources/views/components/portal/ticket-message.blade.php` | Baru: bubble percakapan; juga digunakan sebagai template balasan frontend. |
| `resources/views/components/portal/sidebar.blade.php` | Menghubungkan kedua halaman dan menentukan menu aktif berdasarkan route. |
| `resources/views/components/portal/topbar.blade.php` | Menyesuaikan tujuan tautan bantuan dan notifikasi agar bekerja pada kedua halaman. |
| `resources/views/components/portal/icon.blade.php` | Menambahkan ikon yang diperlukan ke komponen SVG yang ada. |
| `resources/views/user/dashboard.blade.php` | Tautan “Lihat Semua Riwayat” menuju halaman Tiket Saya. |
| `resources/css/tickets.css` | Baru: gaya khusus halaman Tiket Saya, termasuk susunan dua kolom dan responsivitas. |
| `resources/css/app.css` | Mengimpor stylesheet Tiket Saya melalui Vite. |
| `resources/js/tickets.js` | Baru: filter gabungan, pemilihan tiket, navigasi keyboard, draft, balasan, serta lampiran lokal. |
| `resources/js/app.js` | Menggunakan interaksi portal yang sama untuk kedua halaman; memanggil modul tiket hanya saat halaman tiket tersedia. |
| `routes/web.php` | Menambahkan GET `/user/tickets` tanpa mengubah route Beranda. |

Komponen layout, sidebar, topbar, badge, kartu tiket, dialog, toast, font, serta ikon tetap digunakan bersama. Tidak ada dependensi frontend tambahan.

## Interaksi

- **Semua Tiket:** empat laporan; **Sedang Aktif:** dua laporan; **Menunggu Tindakan Saya:** satu laporan; **Telah Selesai:** dua laporan.
- Tab dapat dioperasikan dengan klik, tombol panah kiri/kanan, Home, dan End.
- Pencarian langsung mencocokkan nomor, judul, deskripsi, kategori, status, dan teknisi. Pencarian topbar juga mengarah ke daftar tiket.
- Filter prioritas dapat digabungkan dengan tab dan pencarian. Hasil kosong memiliki tombol reset dan tidak meninggalkan detail tiket yang sudah tersaring.
- Memilih kartu memperbarui metadata, teknisi, timeline, percakapan, dan formulir di kanan. Tautan seperti `/user/tickets#ticket-TK-2038` langsung membuka tiket tersebut.
- Di smartphone, daftar dan detail disusun vertikal. Pemilihan tiket membawa fokus ke detail; tombol kembali membawa pengguna ke daftar.
- Balasan ditambahkan ke percakapan tiket yang dipilih. Draft dan balasan tetap terpisah saat berpindah tiket dalam halaman yang sama.
- Lampiran PNG/JPG maksimal 5 MB dapat dipilih, dihapus, dan dimasukkan ke pesan. Tautan lampiran memakai object URL lokal; file tidak diunggah. Pesan kosong tanpa lampiran ditolak.
- Tiket selesai menampilkan percakapan sebagai arsip. Pengguna tidak mengubah status tiket.

## Batas pratinjau

Semua data merupakan contoh. Pesan, draft, dan lampiran berada di memori browser dan hilang saat halaman dimuat ulang. Tidak ada pengiriman ke teknisi, API percakapan, integrasi n8n, atau perubahan database.

Data halaman Tiket Saya mengikuti screenshot tahap kedua. Data Beranda tetap mengikuti screenshot tahap pertama; kedua kumpulan dummy belum menggunakan sumber tiket bersama. Mulai tahap 5, akses memerlukan login dengan role `user`, dengan identitas akun aktual. Masuk di `/login` atau daftar di `/register`; lihat [panduan autentikasi](authentication.md). Kredensial `.env` dan konfigurasi database tidak diubah.

## Pemeriksaan

- `npm run build`: aset produksi berhasil dibangun.
- `php artisan route:list --path=user`: kedua route tersedia.
- `php artisan view:cache`: Blade berhasil dikompilasi.
- `php artisan test`: dua tes bawaan proyek lulus.
- Pemeriksaan browser: filter, pencarian, detail, timeline, draft, balasan, input aman sebagai teks, validasi lampiran, tautan langsung, serta navigasi mobile.
- Ukuran viewport 320, 360, 390, 768, 820, 1024, 1120, 1280, dan 1440 px diperiksa tanpa overflow horizontal.
- Beranda diuji ulang: menu mobile, pencarian, detail tiket, notifikasi, dan pratinjau AI tetap berjalan.
