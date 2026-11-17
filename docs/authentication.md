# Autentikasi dan RBAC — Tahap 5

Login, registrasi, dan logout menggunakan guard sesi `web` serta model `App\Models\User` bawaan Laravel 13. Tidak ada package autentikasi tambahan. Halaman login/register memakai logo HelpDesk, warna biru/mint, font Inter lokal, Blade, Tailwind, dan Vite yang sudah tersedia.

## Kondisi proyek dan database

- Sebelum perubahan: Laravel 13.42.0, koneksi MySQL aktif, session dan cache menggunakan database, tabel `users` sudah ada tanpa kolom `role`, dan tidak ada Breeze/Fortify/Jetstream atau package permission.
- Migration baru `2026_11_16_000001_add_role_to_users_table` menambahkan enum `user`, `agent`, `admin`, default `user`, dan index role. Kolom lama, password, serta data pengguna yang sudah ada dipertahankan. Migration awal tidak diedit.
- Migration baru sudah diterapkan ke MySQL lokal pada sesi pengembangan ini. Jumlah akun sebelum dan sesudah migration tetap 0; tidak ada akun admin atau password bawaan yang ditanamkan ke database aplikasi.
- Kredensial `.env` dan konfigurasi database tidak berubah. Tidak ada penghapusan tabel, `migrate:fresh`, atau `db:wipe`, termasuk pada proses pengujian yang ditambahkan.

## Menjalankan dan mencoba

Dari `C:\projectss\helpdesk-app`, untuk instalasi/checkout yang belum menjalankan migration tahap ini:

```powershell
php artisan migrate
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Jika PHP belum masuk PATH, gunakan `& 'C:\xampp83\php\php.exe'` sebagai pengganti `php`. Biarkan terminal Laravel berjalan. Untuk hot reload frontend, jalankan `npm run dev` di terminal kedua; URL aplikasi tetap pada port 8000.

1. Buka **http://127.0.0.1:8000/register** dan daftar dengan nama, email, serta password Anda. Password minimal 12 karakter, mengandung huruf besar, huruf kecil, dan angka; maksimal 72 byte agar sesuai bcrypt. Konfirmasi password wajib sama.
2. Registrasi berhasil membuat akun `user`, langsung membuka sesi login, lalu mengarahkan ke `/user/dashboard`. Tidak ada pilihan role dalam formulir.
3. Nama pada topbar, sapaan Beranda, dan identitas pesan baru memakai akun yang sedang masuk. Tiket, perangkat, timeline lama, dan respons AI tetap data contoh; belum ada CRUD tiket atau integrasi AI/n8n.
4. Buka `/admin/dashboard` atau `/agent/dashboard` menggunakan akun user. Aplikasi menampilkan HTTP 403 dengan tautan kembali ke dashboard.
5. Gunakan tombol Keluar. Logout memakai POST dengan CSRF, menghapus sesi autentikasi, dan kembali ke `/login`. Mengunjungi URL portal sesudah logout meminta login kembali.
6. Login menggunakan email/password yang sudah terdaftar. Email dinormalisasi menjadi huruf kecil. Password yang salah mendapatkan pesan umum tanpa membedakan akun tidak ditemukan dengan password keliru.
7. Coba Beranda, Tiket Saya, Buat Tiket Baru, dan Chat dengan AI setelah login. Layout User Portal dipertahankan; semua route tersebut kini memerlukan role `user`.

`/` dan `/dashboard` mengarahkan pengguna ke tujuan yang sesuai. Redirect setelah login selalu mengikuti role dari database, bukan parameter `role`, `redirect`, atau URL tujuan yang dikirim browser.

## Akses per role

| Role | Dashboard setelah login | Area yang boleh dibuka |
| --- | --- | --- |
| `user` | `/user/dashboard` | `/user/dashboard`, `/user/tickets`, `/user/tickets/create`, `/user/chat` |
| `agent` | `/agent/dashboard` | Dashboard Agent sementara |
| `admin` | `/admin/dashboard` | Dashboard Admin sementara |

Akses lintas area ditolak dengan 403, termasuk admin yang membuka area user/agent. Admin belum diberi bypass menyeluruh. Mekanisme ini membatasi route melalui middleware `auth` dan `role`; menyembunyikan menu saja tidak dijadikan perlindungan.

## Membuat admin pertama

Jalankan dari terminal tepercaya pada server/aplikasi yang dimaksud:

```powershell
php artisan helpdesk:create-admin
```

Command meminta nama, email, password tersembunyi, konfirmasi password, lalu konfirmasi pembuatan akun. Tidak ada opsi password pada argumen terminal, password default, atau fallback input password terbuka. Password menggunakan hash yang sama dengan registrasi. Mode noninteraktif ditolak.

Email yang sudah ada ditolak tanpa mengubah password atau role akun tersebut. Command membuat akun baru; tidak mempromosikan pengguna lama. Setelah selesai, masuk di `/login` untuk membuka `/admin/dashboard`.

Untuk mencoba role Agent pada tahap ini, operator pengembangan yang memiliki akses terminal dapat menetapkan role pada **akun uji khusus yang sudah didaftarkan**, melalui Tinker:

```powershell
php artisan tinker
```

```php
$account = App\Models\User::where('email', 'agent-uji@example.com')->firstOrFail();
$account->role = App\Enums\Role::Agent->value;
$account->save();
```

Ganti email contoh dengan akun uji yang benar. Penetapan role ini hanya melalui kode server tepercaya. Belum ada endpoint atau formulir publik untuk mengubah role, dan belum ada antarmuka pengelolaan akun.

## Perlindungan yang diterapkan

- Validasi server untuk nama, email unik, password, dan konfirmasi. Password tidak dimasukkan kembali ke HTML atau flash old input saat validasi gagal.
- Registrasi hanya mengambil `name`, `email`, `password` dari data tervalidasi dan menetapkan role `user` secara eksplisit. Field `role`/`is_admin` ditolak, dan `role` tidak masuk daftar mass assignment model.
- Input password selalu di-hash dengan `Hash::make`, termasuk input yang bentuknya menyerupai hash; model mempertahankan cast `hashed` Laravel. Session ID dan token CSRF diperbarui setelah login/registrasi. Logout membatalkan sesi dan memperbarui token CSRF.
- Semua mutasi autentikasi memakai POST dalam middleware `web`, dengan proteksi request forgery Laravel 13 dan token `@csrf`. GET `/logout` ditolak.
- Lima kegagalan login untuk kombinasi email+IP mengunci percobaan selama 60 detik. Ada batas tambahan 20 POST login per menit/IP dan 5 POST registrasi per menit/IP.
- Remember me menggunakan mekanisme Laravel. Role dibaca dari akun di database pada setiap request; role yang tidak dikenal tidak mendapatkan akses.
- Respons area ber-role memakai `Cache-Control: private, no-store`. Halaman yang dipulihkan dari back/forward cache dimuat ulang agar middleware memeriksa sesi kembali.
- Draft tiket lokal memakai kunci `helpdesk.ticket-create.draft.v1.{userId}`. Akun lain tidak ditawari draft tersebut; logout melalui portal menghapus draft lokal akun itu. Ini tetap simulasi frontend dan tidak menjadi penyimpanan tiket backend.

## File yang dibuat

| File/direktori | Fungsi |
| --- | --- |
| `app/Enums/Role.php` | Tiga role, label, dan tujuan dashboard. |
| `app/Http/Middleware/EnsureRole.php` | Pemeriksaan role dan header cache respons portal. |
| `app/Http/Requests/Auth/LoginRequest.php`, `RegisterRequest.php` | Validasi, normalisasi email, dan pembatasan login. |
| `app/Support/PasswordPolicy.php` | Aturan password bersama untuk registrasi dan command admin. |
| `app/Http/Controllers/Auth/AuthenticatedSessionController.php`, `RegisteredUserController.php` | Login, register, logout, dan pergantian sesi. |
| `app/Console/Commands/CreateAdmin.php` | Pembuatan admin dari terminal secara interaktif. |
| `database/migrations/2026_11_16_000001_add_role_to_users_table.php` | Penambahan role tanpa mengubah migration lama. |
| `routes/auth.php` | Route autentikasi dengan middleware guest/auth dan throttle. |
| `resources/views/layouts/auth.blade.php` | Layout khusus autentikasi; layout User yang lama tidak diubah. |
| `resources/views/auth/login.blade.php`, `register.blade.php` | Form login dan register. |
| `resources/views/components/auth/input.blade.php`, `feedback.blade.php` | Input, label, error validasi, dan status. |
| `resources/views/staff/dashboard.blade.php` | Placeholder Agent dan Admin. |
| `resources/views/errors/403.blade.php` | Tampilan penolakan akses sesuai gaya HelpDesk. |
| `resources/css/auth.css`, `resources/js/auth.js` | Gaya responsif auth, tampil/sembunyikan password, dan pengelolaan form/cache browser. |
| `tests/AuthTestCase.php`, `tests/Concerns/UsesIsolatedDatabase.php` | Dasar pengujian dengan pengaman database SQLite in-memory. |
| `tests/Feature/Auth/*.php`, `tests/Feature/RoleMigrationTest.php` | Tes autentikasi, command admin, RBAC, dan preservasi data migration. |
| `docs/authentication.md` | Panduan tahap ini. |

## File yang diperbarui

- `app/Models/User.php`: default role `user`, tujuan dashboard, dan identitas portal; role tetap tidak mass assignable.
- `bootstrap/app.php`: alias middleware role dan redirect guest/pengguna yang sudah login.
- `routes/web.php`: proteksi empat route User, dashboard Agent/Admin, dan redirect halaman awal.
- Empat controller pada `app/Http/Controllers/User/`: identitas akun dari request yang sudah terautentikasi; data layanan tetap simulasi.
- `database/factories/UserFactory.php`: state role untuk fixture pengujian.
- `resources/views/components/portal/sidebar.blade.php`: tombol Keluar menjadi form POST tanpa mengubah kelas/penampilannya.
- `resources/css/app.css`, `resources/js/app.js`: import modul autentikasi, profil aktual, dan pembersihan draft saat logout.
- `resources/js/ticket-create.js`: kunci draft lokal dipisahkan per akun.
- `phpunit.xml`: memastikan pengujian memakai SQLite in-memory, session/cache array, dan tidak mengikuti kredensial database aplikasi.
- `tests/Feature/ExampleTest.php`: halaman awal kini mengarahkan tamu ke login.
- Dokumentasi empat halaman User dan `public/build/`: panduan akses terbaru serta aset produksi.

## Pemeriksaan

```powershell
php artisan test
php artisan route:list -v --except-vendor
php artisan view:cache
npm run build
```

75 tes backend lulus dengan 816 assertion. Tes meliputi registrasi publik, penolakan injeksi role melalui body/query, mass assignment, hashing termasuk input berbentuk hash, validasi, duplikasi email, remember me, session rotation, logout, CSRF nyata, rate limiting, seluruh kombinasi akses role, perubahan role pada sesi aktif, command admin, dan data pengguna lama setelah migration.

Tes database menolak berjalan jika konfigurasi tidak memakai SQLite `:memory:` atau masih menggunakan config cache. Setiap tes menjalankan migration biasa pada database kosong di memori; tidak menggunakan trait yang menjalankan `migrate:fresh`.

Pemeriksaan browser menggunakan database SQLite QA terpisah dan server sementara port 8001. Akun browser QA tidak dibuat di MySQL aplikasi. Seluruh 38 pemeriksaan autentikasi browser lulus pada 320–1440 px, termasuk validasi server, password toggle, redirect, logout, penolakan lintas-role, draft per akun, dan navigasi. Regresi Beranda, Tiket Saya, Buat Tiket Baru, dan 54 pemeriksaan Chat dengan AI juga lulus tanpa error JavaScript/aset. Skrip serta screenshot lokal berada di `C:\projectss\.qa-helpdesk`.

Implementasi mengikuti [autentikasi sesi Laravel 13](https://laravel.com/docs/13.x/authentication) dan [registrasi middleware Laravel 13](https://laravel.com/docs/13.x/middleware).
