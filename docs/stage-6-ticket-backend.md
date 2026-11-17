# Tahap 6 — CRUD tiket dan integrasi database

## Hasil audit

Proyek menggunakan Laravel 13, PHP 8.3, Blade, Vite, dan koneksi `mysql`. Server lokal menggunakan MariaDB yang kompatibel dengan koneksi MySQL Laravel. Sebelum tahap ini, database hanya memiliki tabel bawaan Laravel dan tambahan role pada `users`. Halaman Beranda, Tiket Saya, serta Buat Tiket memakai array contoh; pesan hanya ditambahkan ke DOM dan draft memakai sessionStorage.

Route dan controller yang sudah ada dipakai kembali. Kartu tiket, komponen formulir, layout, sidebar, topbar, font, serta warna portal dipertahankan. Autentikasi dan middleware role tetap digunakan. Workspace agent/admin hanya mendapat tautan Penanganan Tiket dan kontrol yang diperlukan pada detail tiket; dashboard admin lengkap tidak dibuat. AI tetap menggunakan simulasi lokal dan tidak terhubung ke n8n.

## Database dan instalasi

Migration baru: `database/migrations/2026_11_17_000001_create_ticket_tables.php`.

Migration membuat lima tabel dalam urutan dependensi:

- `categories`: kategori, slug unik, ikon, deskripsi.
- `tickets`: nomor unik, pemilik, kategori, agent, judul, deskripsi, prioritas, status, departemen, lokasi, waktu penyelesaian, timestamps. Kategori boleh kosong selama draft; wajib saat dikirim.
- `ticket_messages`: pesan tiket dan pengirim.
- `ticket_attachments`: metadata berkas, tiket, pesan opsional, dan pengunggah.
- `ticket_status_histories`: status sebelum/sesudah, pelaku, catatan, timestamps.

Foreign key menjaga relasi. Index mencakup pemilik/status, agent/status, prioritas/status, dan urutan aktivitas tiket. Nomor memakai prefix `TK-` dengan ULID serta unique constraint, tanpa menghitung jumlah tiket sebelumnya.

Untuk checkout/database lain, jalankan dari direktori proyek:

```powershell
php artisan migrate --no-interaction
php artisan db:seed --class=CategorySeeder --no-interaction
php artisan migrate:status
npm.cmd run build
```

Migration dan CategorySeeder sudah dijalankan pada MySQL lokal saat implementasi ini. Seeder hanya menambahkan kategori yang belum ada berdasarkan slug; aman diulang dan tidak mengubah kategori yang sudah ada. Jangan memakai DatabaseSeeder bawaan untuk kebutuhan tahap ini karena seeder tersebut membuat akun contoh.

Tidak ada perubahan kredensial `.env`, penghapusan data development, atau perintah reset database. Data akun dan tiket QA berada di database terpisah.

## Hak akses dan status

`TicketPolicy` memeriksa seluruh operasi tiket:

| Role | Hak akses |
| --- | --- |
| User | Membuat tiket; membaca tiket sendiri; mengedit, mengirim, dan menghapus draft sendiri; berkirim pesan dan mengunduh lampiran tiket sendiri. |
| Agent | Melihat dan menangani tiket non-draft yang ditugaskan kepadanya, mengirim pesan, mengubah status sesuai alur, dan mengunduh lampirannya. |
| Admin | Melihat semua tiket, menugaskan/melepas agent pada tiket non-draft yang belum ditutup, mengelola status sesuai alur, serta mengakses percakapan/lampiran. |

Draft hanya dapat diubah/dikirim/dihapus pemiliknya. User tidak dapat menetapkan status penanganan atau agent. Akun dengan role lain ditolak. Setelah penugasan dipindah, agent sebelumnya kehilangan akses tiket dan unduhan.

Alur status:

```text
draft -> open -> in_progress -> waiting_user -> resolved -> closed
                   |                |
                   +--> resolved    +--> in_progress
resolved -> in_progress (buka kembali untuk investigasi)
```

`closed` merupakan arsip akhir; pesan baru dinonaktifkan. Pesan masih boleh dikirim ketika `resolved`, sehingga pemilik dapat memberikan konfirmasi sebelum penutupan oleh tim IT. Penugasan tidak otomatis mengubah status.

Riwayat mencatat status awal dan setiap perubahan berikutnya, termasuk pengiriman draft. `resolved_at` diisi saat resolved, dikosongkan jika kembali diproses, dan dipertahankan saat closed. Mengedit isi draft tanpa mengubah status tidak menambahkan riwayat status.

## Validasi, transaksi, dan lampiran

- Form Request memvalidasi judul 5–150 karakter dan deskripsi 20–5.000 karakter saat dikirim. Draft boleh belum lengkap. Prioritas: low, medium, high, urgent. Departemen/lokasi opsional.
- Input `user_id`, `ticket_number`, `resolved_at`, dan field sensitif lain ditolak dari form umum. Status dan penugasan hanya diterima endpoint khusus berizin.
- Operasi beberapa tabel memakai transaksi. TicketService mengunci tiket dan memeriksa Policy kembali setelah lock untuk mencegah penggunaan status/penugasan lama.
- Lampiran: PNG, JPG/JPEG, PDF, atau LOG teks; maksimum 5 MB per berkas dan 5 berkas per pengiriman. Draft dibatasi 5 lampiran total. Isi MIME harus sesuai ekstensi. File kosong dan file dengan tipe palsu ditolak.
- Disk `ticket_private` memakai `storage/app/ticket-attachments`, di luar public dan di luar root disk local yang mendukung URL sementara. `serve` dinonaktifkan. Tidak memerlukan `storage:link`.
- Unduhan hanya melalui `/ticket-attachments/{attachment}/download`, memeriksa TicketPolicy, dan memakai `attachment`, `nosniff`, serta `private, no-store`.
- Kegagalan transaksi menghapus berkas baru yang sudah ditulis. Menghapus draft juga menghapus metadata, riwayat, dan berkasnya.
- Setelah validasi gagal, browser perlu memilih ulang file. Input teks dipertahankan melalui `old()`.

Daftar dan statistik dihitung dari query yang dibatasi role/pemilik. Pencarian nomor/judul serta filter status/prioritas berjalan di server, dengan pagination 10 tiket. Statistik Beranda menghitung semua tiket pemilik termasuk draft; kartu aktif mencakup open, in_progress, waiting_user dan menampilkan lima tiket terbaru.

## Route aplikasi

| Method | Path | Fungsi |
| --- | --- | --- |
| GET | `/user/dashboard` | Statistik dan tiket aktif pemilik. |
| GET / POST | `/user/tickets` | Daftar pribadi / simpan tiket atau draft. |
| GET | `/user/tickets/create` | Form tiket baru. |
| GET | `/user/tickets/{ticket}/edit` | Edit draft pemilik. |
| PATCH / DELETE | `/user/tickets/{ticket}` | Simpan edit / hapus draft. |
| POST | `/user/tickets/{ticket}/submit` | Kirim draft tersimpan. |
| GET | `/support/tickets` | Daftar agent/admin sesuai Policy. |
| GET | `/tickets/{ticket}` | Detail bersama dengan pemeriksaan Policy. |
| POST | `/tickets/{ticket}/messages` | Kirim pesan dan lampiran. |
| PATCH | `/tickets/{ticket}/status` | Perubahan status agent/admin. |
| PATCH | `/tickets/{ticket}/assignment` | Penugasan agent oleh admin. |
| GET | `/ticket-attachments/{attachment}/download` | Unduhan private. |

Semua operasi tulis menggunakan middleware web/auth/role dan token CSRF; form PATCH/DELETE memakai method spoofing Laravel. Endpoint tidak menerima role dari input pengguna.

## Pengujian otomatis

```powershell
php artisan test --compact
php artisan route:list --except-vendor
php artisan view:cache
php artisan view:clear
npm.cmd run build
```

`phpunit.xml` memaksa SQLite `:memory:` sebagai database pengujian. Trait `UsesIsolatedDatabase` menolak database lain maupun config cache sebelum migration test dijalankan. Test memakai migration tambahan pada database memori baru, bukan reset database development. Lampiran pengujian memakai `Storage::fake('ticket_private')`.

Hasil akhir: 95 test lulus (1.151 assertions), termasuk 20 feature test tiket. Pemeriksaan route mencatat 24 route aplikasi tanpa duplikasi method/path; kompilasi Blade dan build Vite berhasil. Browser QA: 20 pemeriksaan lulus, tidak ada exception JavaScript atau request HTTP gagal, dengan pemeriksaan lebar layar 320?1440 px.

`tests/Feature/Tickets/TicketWorkflowTest.php` mencakup pembuatan, draft parsial, edit/pengiriman, hapus, pagination/pencarian/filter/statistik, akses lintas pemilik, pembatasan agent, pengelolaan admin, transisi status, waktu penyelesaian, pesan, XSS escaping, unduhan private, input sensitif, MIME/ekstensi/ukuran/jumlah file, rollback ketika disk gagal, penugasan lama, akses tamu, CSRF, serta seeder idempoten. Tes gambar menggunakan fixture PNG kecil sehingga tidak memerlukan ekstensi GD.

Pemeriksaan browser memakai database SQLite QA tersendiri, akun QA terpisah, dan server localhost terpisah. Artefak lokal berada di `C:/projectss/.qa-helpdesk/stage6-*`; bukan akun/data MySQL development.

## Cara menguji di browser

1. Jalankan MySQL dan `php artisan serve`, lalu buka URL yang dicetak. Registrasikan akun user bila database belum memiliki pengguna.
2. Buka Buat Tiket Baru. Isi sebagian lalu pilih Simpan Sebagai Draft. Buka tab Draft, muat ulang, dan pastikan draft tetap ada.
3. Buka Edit Draft, lengkapi kategori/judul/deskripsi, pilih prioritas dan lampiran, lalu simpan draft atau langsung kirim. Tombol Kirim Draft pada detail juga dapat dipakai setelah isi lengkap.
4. Pada Tiket Saya, cari judul/nomor, filter status/prioritas, buka detail, dan periksa statistik Beranda. Draft lain dapat dihapus; tiket yang sudah dikirim tidak memiliki tombol edit/hapus.
5. Kirim pesan dan lampiran, muat ulang, lalu unduh kembali berkas. Uji file di atas 5 MB atau ekstensi tidak diizinkan untuk melihat validasi.
6. Siapkan akun admin melalui perintah `php artisan helpdesk:create-admin` yang sudah ada. Untuk akun agent, gunakan Tinker pada akun yang memang disiapkan menjadi IT Support: `php artisan tinker`, lalu `$agent = App\Models\User::where('email', 'email-agent-yang-sudah-terdaftar')->firstOrFail(); $agent->role = 'agent'; $agent->save();`. Registrasi publik selalu menghasilkan role user.
7. Login admin, buka Penanganan Tiket, pilih tiket open, pilih agent, simpan penugasan. Login agent tersebut dan ubah status ke Sedang Diproses. Kirim pesan, pilih Menunggu Respon atau Selesai, sertakan catatan, dan periksa riwayat.
8. Login user pemilik untuk membalas/konfirmasi. Login admin atau agent penanggung jawab untuk menutup tiket yang sudah resolved. Setelah closed, percakapan menjadi arsip.
9. Gunakan akun user lain atau agent yang tidak ditugaskan. Akses URL detail/unduhan tiket tadi harus menghasilkan 403. Daftar dan statistik tidak boleh menampilkan tiket di luar akses akun tersebut.

## Daftar file

File baru:

- `app/Enums/TicketStatus.php`
- `app/Models/Category.php`, `Ticket.php`, `TicketMessage.php`, `TicketAttachment.php`, `TicketStatusHistory.php`
- `app/Policies/TicketPolicy.php`
- `app/Services/TicketService.php`
- `app/Support/TicketPresenter.php`
- `app/Http/Controllers/TicketActivityController.php`
- `app/Http/Requests/Tickets/TicketInput.php`, `SaveTicketRequest.php`, `SubmitDraftRequest.php`, `TicketIndexRequest.php`, `TicketMessageRequest.php`, `TicketStatusRequest.php`, `AssignTicketRequest.php`
- `database/migrations/2026_11_17_000001_create_ticket_tables.php`
- `database/seeders/CategorySeeder.php`
- `tests/Feature/Tickets/TicketWorkflowTest.php`
- `docs/stage-6-ticket-backend.md`

File diubah:

- `app/Models/User.php`
- `app/Http/Controllers/User/DashboardController.php`, `TicketController.php`, `CreateTicketController.php`
- `routes/web.php`, `config/filesystems.php`
- `resources/views/layouts/user.blade.php`
- `resources/views/user/dashboard.blade.php`, `tickets.blade.php`, `tickets-create.blade.php`
- `resources/views/staff/dashboard.blade.php`
- `resources/views/components/portal/sidebar.blade.php`, `topbar.blade.php`, `ticket-card.blade.php`, `ticket-detail.blade.php`, `ticket-message.blade.php`, `file-upload.blade.php`
- `resources/js/app.js`, `tickets.js`, `ticket-create.js`, `file-upload.js`
- `resources/css/tickets.css`
- Output build Vite di `public/build/` diperbarui.

Referensi kompatibilitas: [Laravel 13 Authorization](https://laravel.com/framework/docs/13.x/authorization) dan [Laravel 13 File Storage](https://laravel.com/framework/docs/13.x/filesystem).
