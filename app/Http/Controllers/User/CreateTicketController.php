<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Ticket;
use Illuminate\Support\Facades\Gate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CreateTicketController extends Controller
{
    public function __invoke(Request $request): View
    {
        Gate::authorize('create', Ticket::class);
        return $this->form($request);
    }

    public function edit(Request $request, Ticket $ticket): View
    {
        Gate::authorize('update', $ticket);
        return $this->form($request, $ticket);
    }

    private function form(Request $request, ?Ticket $ticket = null): View
    {
        return view('user.tickets-create', [
            'user' => $request->user()->portalIdentity(),
            'ticket' => $ticket,
            'categories' => Category::orderBy('id')->get()->map(fn ($category) => [
                'value' => (string) $category->id, 'slug' => $category->slug,
                'label' => $category->name, 'hint' => $category->description, 'icon' => $category->icon,
            ])->all(),
            'priorities' => [
                ['value' => 'low', 'label' => 'Rendah', 'description' => 'Kendala minor, masih dapat melakukan tugas kerja harian lainnya.'],
                ['value' => 'medium', 'label' => 'Sedang', 'description' => 'Sebagian aktivitas terganggu, ada hambatan pada alur kerja utama.'],
                ['value' => 'high', 'label' => 'Tinggi', 'description' => 'Pekerjaan lumpuh total, berdampak pada tenggat waktu mendesak.'],
                ['value' => 'urgent', 'label' => 'Mendesak', 'description' => 'Gangguan kritis yang memerlukan penanganan segera.'],
            ],
            'departments' => ['Marketing & Growth', 'Product Design', 'Engineering', 'Human Resources', 'Finance & Accounting', 'Operations'],
            'locations' => ['WFH (Work From Home) – Jakarta Selatan', 'Kantor Pusat – Lantai 3', 'Kantor Pusat – Lantai 4, Wing B', 'Kantor Cabang – Bandung', 'Remote / Lokasi Lain'],
            'defaults' => [
                'category_id' => old('category_id', $ticket?->category_id ?? ''),
                'title' => old('title', $ticket?->title ?? ''),
                'priority' => old('priority', $ticket?->priority ?? 'medium'),
                'department' => old('department', $ticket?->department ?? ''),
                'location' => old('location', $ticket?->location ?? ''),
                'description' => old('description', $ticket?->description ?? ''),
            ],
            'demoAttachment' => null,
            'suggestions' => [
                'vpn' => [
                    'keyword' => 'VPN',
                    'steps' => [
                        ['title' => 'Restart Service GlobalProtect', 'body' => 'Tutup dan buka kembali GlobalProtect. Jika tersedia, gunakan opsi restart service sesuai panduan tim IT.'],
                        ['title' => 'Periksa Sertifikat SSO Okta', 'body' => 'Pastikan sesi login browser perusahaan Anda belum kedaluwarsa. Keluar lalu masuk kembali ke portal SSO.'],
                        ['title' => 'Gunakan Gateway Cadangan', 'body' => 'Jika disediakan tim IT, pilih gateway cadangan yang disetujui. Contoh: vpn-backup.corp.internal.'],
                    ],
                    'articles' => [
                        ['title' => 'Panduan Konfigurasi GlobalProtect VPN', 'body' => 'Contoh panduan: periksa koneksi internet, buka GlobalProtect, lalu gunakan portal dan akun SSO resmi perusahaan. Catat kode error jika koneksi masih gagal.'],
                        ['title' => 'Jadwal Pemeliharaan Jaringan Kantor', 'body' => 'Contoh informasi: jadwal pemeliharaan akan diumumkan oleh tim IT melalui kanal internal. Tidak ada jadwal operasional langsung pada pratinjau ini.'],
                        ['title' => 'Prosedur Penanganan Laptop & Perangkat', 'body' => 'Siapkan asset tag perangkat, waktu kejadian, langkah yang sudah dicoba, dan tangkapan layar kendala untuk mempercepat pemeriksaan teknisi.'],
                    ],
                ],
                'hardware' => [
                    'keyword' => 'Hardware',
                    'steps' => [
                        ['title' => 'Periksa Koneksi Perangkat', 'body' => 'Periksa sambungan daya dan kabel perangkat. Pastikan aksesori terpasang dengan baik.'],
                        ['title' => 'Coba Mulai Ulang', 'body' => 'Simpan pekerjaan Anda sebelum memulai ulang perangkat. Catat apakah kendala kembali muncul.'],
                        ['title' => 'Siapkan Asset Tag', 'body' => 'Catat identitas perangkat dan gejala yang muncul agar tim teknisi dapat menindaklanjuti.'],
                    ],
                    'articles' => [['title' => 'Pemeriksaan Awal Perangkat Kerja', 'body' => 'Periksa daya, kabel, dan perangkat pendukung. Hindari membongkar perangkat; sertakan asset tag pada laporan.']],
                ],
                'software' => [
                    'keyword' => 'Software',
                    'steps' => [
                        ['title' => 'Catat Nama & Versi Aplikasi', 'body' => 'Sertakan aplikasi yang bermasalah dan pesan error lengkap pada deskripsi.'],
                        ['title' => 'Buka Ulang Aplikasi', 'body' => 'Simpan dokumen, tutup aplikasi, lalu periksa apakah kendala masih berulang.'],
                        ['title' => 'Periksa Akses Lisensi', 'body' => 'Pastikan Anda masuk menggunakan akun perusahaan yang memiliki lisensi aplikasi.'],
                    ],
                    'articles' => [['title' => 'Panduan Kendala Software & Lisensi', 'body' => 'Gunakan aplikasi dari katalog perusahaan. Sertakan nama aplikasi, versi, dan kebutuhan lisensi pada laporan Anda.']],
                ],
                'network' => [
                    'keyword' => 'Jaringan',
                    'steps' => [
                        ['title' => 'Periksa Koneksi Wi-Fi', 'body' => 'Pastikan perangkat tersambung ke jaringan yang benar dan mode pesawat tidak aktif.'],
                        ['title' => 'Uji Akses Internet', 'body' => 'Coba akses situs publik untuk membedakan kendala internet dengan layanan internal.'],
                        ['title' => 'Catat Lokasi & Waktu', 'body' => 'Sertakan lokasi kerja, nama jaringan, serta waktu mulai terjadinya gangguan.'],
                    ],
                    'articles' => [['title' => 'Panduan Koneksi Wi-Fi Korporat', 'body' => 'Gunakan jaringan resmi perusahaan dan verifikasi akun sesuai panduan internal. Catat pesan error jika koneksi gagal.']],
                ],
                'access' => [
                    'keyword' => 'Akses & Akun',
                    'steps' => [
                        ['title' => 'Periksa Akun yang Digunakan', 'body' => 'Pastikan Anda menggunakan akun perusahaan untuk mengakses layanan internal.'],
                        ['title' => 'Cek Hak Akses yang Dibutuhkan', 'body' => 'Tuliskan nama sistem dan peran akses yang diperlukan, tanpa menyertakan kata sandi.'],
                        ['title' => 'Siapkan Persetujuan Manajer', 'body' => 'Untuk akses baru, siapkan persetujuan manajer jika diwajibkan oleh proses internal.'],
                    ],
                    'articles' => [['title' => 'Panduan Permintaan Akses & Akun', 'body' => 'Jelaskan sistem, peran, dan alasan kebutuhan akses. Lampirkan persetujuan jika diperlukan; jangan sertakan password atau OTP.']],
                ],
                'email' => [
                    'keyword' => 'Email & SSO',
                    'steps' => [
                        ['title' => 'Periksa Sesi Login SSO', 'body' => 'Buka portal resmi perusahaan dan pastikan sesi akun Anda masih aktif.'],
                        ['title' => 'Periksa Verifikasi MFA', 'body' => 'Pastikan waktu perangkat benar dan metode verifikasi akun dapat digunakan.'],
                        ['title' => 'Gunakan Pemulihan Akun Resmi', 'body' => 'Jika lupa kata sandi, gunakan opsi pemulihan akun resmi. Jangan bagikan kode OTP.'],
                    ],
                    'articles' => [['title' => 'Pemulihan Email & Login SSO', 'body' => 'Gunakan tautan pemulihan dari portal resmi perusahaan. Hubungi tim IT jika metode verifikasi Anda tidak lagi dapat diakses.']],
                ],
            ],
        ]);
    }
}
