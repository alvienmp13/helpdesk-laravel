# Buat Tiket Baru

Halaman **`/user/tickets/create`** memakai route `user.tickets.create` dan mengikuti `design/references/user-buat-tiket.png`. Sidebar, topbar, layout, dialog, toast, badge, tipografi, serta ikon digunakan kembali dari portal sebelumnya.

## Membuka dan menguji

Mulai tahap 5, halaman ini memerlukan login dengan role `user`. Registrasi tersedia di `/register`; lihat [panduan autentikasi](authentication.md).

Jika Laravel sudah berjalan, buka **http://127.0.0.1:8000/user/tickets/create**. Tombol Buat Tiket Baru di sidebar, kartu layanan Beranda, dan header Tiket Saya sudah menuju halaman ini. Riwayat Tiket Terakhir kembali ke Tiket Saya.

Jika server belum berjalan, dari folder `helpdesk-app`:

```sh
php artisan serve --host=127.0.0.1 --port=8000
```

Untuk hot reload, jalankan `npm run dev` di terminal kedua. Tetap buka halaman pada port 8000; Vite hanya menyediakan CSS/JavaScript. Untuk memakai aset produksi, hentikan Vite lalu jalankan `npm run build`, dengan Laravel tetap berjalan.

Langkah evaluasi:

1. Perhatikan formulir contoh VPN yang terisi sesuai referensi. Ganti kategori Hardware, Software, Jaringan, Akses & Akun, atau Email & SSO untuk melihat perubahan pilihan dan saran simulasi.
2. Pada kategori Jaringan, ketik judul dengan kata `VPN` atau `GlobalProtect` untuk menampilkan saran VPN. Judul lainnya menampilkan saran jaringan umum.
3. Ubah prioritas, departemen, dan lokasi. Kosongkan judul/deskripsi lalu tekan Kirim Tiket untuk memeriksa validasi. Judul menerima 5–150 karakter dan deskripsi 20–5.000 karakter, setelah spasi tepi dibuang.
4. Gunakan toolbar untuk menambahkan penanda format Markdown dan daftar. Gunakan Template Masalah menambahkan panduan kronologi tanpa menghapus deskripsi yang sudah ada.
5. Pilih atau jatuhkan berkas ke area lampiran. Klik nama berkas untuk preview dan tombol silang untuk menghapusnya.
6. Tekan Simpan Sebagai Draft. Draft teks disimpan di `sessionStorage` untuk tab ini. Refresh halaman, lalu pilih Muat Draft. File lampiran perlu dipilih ulang. Hapus hanya menghapus draft lokal, bukan isian formulir yang sedang tampil.
7. Lengkapi formulir lalu tekan Kirim Tiket Bantuan. Dialog menampilkan ringkasan dan menyatakan bahwa pengiriman hanya simulasi; tidak ada tiket yang dibuat di database.

## Lampiran

- Maksimal 5 file, masing-masing maksimal 15 MB. Format yang diterima: PNG, JPG/JPEG, PDF, dan LOG.
- Ekstensi dan MIME diperiksa. File kosong, tipe tidak sesuai, file terlalu besar, dan duplikat ditolak dengan pesan yang menyebut nama file.
- Contoh `screenshot_globalprotect_error_gp502.png` hanya metadata tampilan dari referensi, bukan file yang sudah diunggah. Contoh ini diganti ketika berkas asli pertama ditambahkan dan tidak dihitung sebagai lampiran kiriman.
- PNG/JPG mempunyai thumbnail dan preview gambar. LOG ditampilkan sebagai teks aman, dibatasi 20 KB untuk preview. PDF dapat dibuka melalui tautan lokal di tab browser baru.
- File menggunakan object URL di browser. Tidak ada upload, POST, endpoint penyimpanan, atau penulisan file lampiran ke server.

## Data simulasi

Saran AI, artikel, SLA, status service desk, dan kontak darurat memakai data contoh. Tidak ada API AI atau integrasi n8n. Tombol solusi mandiri hanya menampilkan respons simulasi dan tidak membuat, menutup, atau mengubah tiket.

Draft menggunakan kunci `helpdesk.ticket-create.draft.v1.{userId}` di `sessionStorage`, hanya menyimpan isian teks serta waktu simpan. Draft dipisahkan per akun dan dihapus saat logout melalui portal. Draft tidak disimpan ke database dan tidak menyertakan isi file. Ketika penyimpanan browser gagal, halaman menampilkan pesan kegagalan, bukan pesan berhasil.

## File baru

| File | Fungsi |
| --- | --- |
| `app/Http/Controllers/User/CreateTicketController.php` | Data kategori, prioritas, departemen, lokasi, nilai contoh, serta saran dan artikel simulasi. |
| `resources/views/user/tickets-create.blade.php` | Susunan formulir tiga bagian dan panel AI di kanan. |
| `resources/views/components/portal/form-section.blade.php` | Kartu bagian formulir dengan nomor langkah. |
| `resources/views/components/portal/file-upload.blade.php` | Area drag-and-drop, daftar berkas, template preview, dan pesan validasi. |
| `resources/css/ticket-create.css` | Gaya formulir, kategori, prioritas, upload, saran, dan responsivitas. |
| `resources/js/ticket-create.js` | Validasi, draft lokal, format deskripsi, saran dinamis, dan respons simulasi. |
| `resources/js/file-upload.js` | Validasi lampiran, preview aman, hapus berkas, dan pelepasan object URL. |

## File yang diperbarui

- `routes/web.php`: menambahkan route GET halaman create.
- `resources/css/app.css`: mengimpor stylesheet baru melalui Vite.
- `resources/js/app.js`: menginisialisasi modul formulir pada halaman create dan menyediakan konten dialog untuk preview lokal.
- `resources/views/components/portal/sidebar.blade.php`: tautan create dengan penanda halaman aktif.
- `resources/views/components/portal/service-card.blade.php`: kartu layanan Beranda menuju formulir yang sebenarnya.
- `resources/views/user/tickets.blade.php`: tautan create pada header Tiket Saya.
- `resources/views/components/portal/icon.blade.php`: menambahkan ikon formulir ke sistem ikon yang sudah ada.
- Dokumentasi Beranda dan Tiket Saya: memperbarui keterangan halaman create.
- `public/build/`: aset hasil build Vite.

Tidak ada dependensi tambahan untuk formulir. Autentikasi dan migration role ditambahkan terpisah pada tahap 5; penyimpanan tiket backend belum diimplementasikan. Kredensial `.env` dan konfigurasi database tetap dipertahankan.

## Pemeriksaan

```sh
npm run build
php artisan route:list --path=user
php artisan view:cache
php artisan test
```

Pemeriksaan browser mencakup viewport 320–1440 px, validasi enam field, pilihan kategori/prioritas, saran simulasi, draft dan pemulihannya, preview lampiran, drag-and-drop, tipe/ukuran/jumlah/duplikasi berkas, keamanan teks, serta navigasi ketiga halaman. Beranda dan Tiket Saya diperiksa ulang untuk menjaga fungsi sebelumnya.
