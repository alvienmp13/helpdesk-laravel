<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __invoke(Request $request): View
    {
        // Display fixtures only: no AI provider, chat endpoint, or database access.
        $user = $request->user()->portalIdentity();
        $welcome = "Halo {$user['name']}! 👋 Saya asisten virtual IT HelpDesk Anda. Apakah ada kendala dengan perangkat laptop, konektivitas VPN, atau aplikasi internal kantor hari ini?";
        $replies = [
            'vpn' => [
                'text' => 'Terima kasih atas rinciannya. Untuk contoh error 504 Connection Timed Out pada GlobalProtect setelah update Windows, berikut alur pemeriksaan awal yang dapat ditinjau bersama tim IT. Ini adalah jawaban demo, bukan hasil diagnosis perangkat Anda.',
                'tag' => 'Solusi Diagnostik', 'guide' => 'Langkah Pemulihan Mandiri (Contoh):',
                'steps' => [
                    ['title' => 'Periksa Gateway', 'body' => 'Cocokkan alamat portal dengan panduan resmi perusahaan. Alamat contoh pada desain: vpn.corp.company.id.', 'checked' => true],
                    ['title' => 'Flush DNS & Reset Winsock', 'body' => 'Contoh perintah dari referensi desain. Tinjau dengan tim IT sebelum digunakan; demo ini tidak menjalankannya.', 'code' => 'ipconfig /flushdns && netsh winsock reset'],
                    ['title' => 'Restart Service', 'body' => 'Jika diizinkan tim IT, buka ulang GlobalProtect dan coba koneksi kembali. Catat kode error jika masih muncul.'],
                ],
            ],
            'password' => [
                'text' => 'Berikut contoh langkah pemulihan akun SSO. Gunakan portal resmi perusahaan dan jangan membagikan kata sandi atau kode OTP dalam percakapan.',
                'tag' => 'Panduan Akun', 'guide' => 'Pemulihan Akun (Demo):',
                'steps' => [
                    ['title' => 'Buka Portal SSO Resmi', 'body' => 'Pilih opsi Lupa kata sandi pada halaman login perusahaan.'],
                    ['title' => 'Verifikasi Identitas', 'body' => 'Gunakan metode pemulihan atau MFA yang terdaftar pada akun Anda.'],
                    ['title' => 'Hubungi Tim IT jika Terhambat', 'body' => 'Jika metode verifikasi sudah tidak dapat diakses, buat tiket kategori Akses & Akun.'],
                ],
            ],
            'wifi' => [
                'text' => 'Saya dapat menunjukkan alur pemeriksaan koneksi Wi-Fi sebagai demo. Mulai dari koneksi perangkat, kemudian bedakan gangguan internet dengan layanan internal.',
                'tag' => 'Panduan Jaringan', 'guide' => 'Pemeriksaan Koneksi (Demo):',
                'steps' => [
                    ['title' => 'Periksa Jaringan yang Dipilih', 'body' => 'Pastikan Wi-Fi aktif, mode pesawat mati, dan nama jaringan sesuai panduan perusahaan.'],
                    ['title' => 'Uji Akses Internet', 'body' => 'Coba situs publik untuk melihat apakah masalah hanya terjadi pada aplikasi kantor.'],
                    ['title' => 'Catat Lokasi & Pesan Error', 'body' => 'Sertakan lokasi kerja, waktu kejadian, dan pesan error jika perlu diteruskan ke teknisi.'],
                ],
            ],
            'email' => [
                'text' => 'Untuk contoh kendala Outlook atau sinkronisasi email, berikut tiga hal yang bisa diperiksa. Tidak ada akses ke akun email Anda dalam demo ini.',
                'tag' => 'Panduan Email', 'guide' => 'Sinkronisasi Email (Demo):',
                'steps' => [
                    ['title' => 'Periksa Koneksi & Mode Offline', 'body' => 'Pastikan internet tersedia dan aplikasi email tidak berada dalam mode offline.'],
                    ['title' => 'Uji Melalui Webmail', 'body' => 'Masuk ke webmail resmi perusahaan untuk membedakan kendala akun dengan aplikasi.'],
                    ['title' => 'Catat Pesan Sinkronisasi', 'body' => 'Simpan kode error dan waktu terakhir email tersinkronisasi untuk laporan tim IT.'],
                ],
            ],
            'software' => [
                'text' => 'Permintaan software atau lisensi dapat disiapkan melalui tiket bantuan IT. Berikut contoh informasi yang perlu dilengkapi; demo ini belum mengajukan atau mengaktifkan lisensi.',
                'tag' => 'Permintaan Software', 'guide' => 'Persiapan Permintaan (Demo):',
                'steps' => [
                    ['title' => 'Sebutkan Software & Versi', 'body' => 'Tuliskan aplikasi, kebutuhan lisensi, dan perangkat yang akan digunakan.'],
                    ['title' => 'Jelaskan Kebutuhan Kerja', 'body' => 'Cantumkan alasan penggunaan dan tim atau proyek terkait.'],
                    ['title' => 'Siapkan Persetujuan', 'body' => 'Lampirkan persetujuan manajer jika diwajibkan, lalu gunakan formulir Buat Tiket Baru.'],
                ],
            ],
            'hardware' => [
                'text' => 'Berikut panduan awal untuk contoh kendala laptop, monitor, baterai, atau docking station. Saya tidak memeriksa perangkat secara langsung dalam demo ini.',
                'tag' => 'Panduan Perangkat', 'guide' => 'Pemeriksaan Perangkat (Demo):',
                'steps' => [
                    ['title' => 'Periksa Daya & Sambungan', 'body' => 'Periksa adaptor daya dan kabel yang tersambung ke perangkat.'],
                    ['title' => 'Catat Gejala Kendala', 'body' => 'Tuliskan kapan masalah terjadi dan perubahan terakhir sebelum kendala muncul.'],
                    ['title' => 'Siapkan Asset Tag', 'body' => 'Cantumkan identitas perangkat pada tiket agar tim IT dapat menindaklanjuti.'],
                ],
            ],
            'printer' => [
                'text' => 'Untuk contoh kendala printer kantor, periksa koneksi dan antrean cetak lebih dahulu. Konfigurasi driver atau alamat printer perlu mengikuti panduan internal.',
                'tag' => 'Panduan Printer', 'guide' => 'Pemeriksaan Printer (Demo):',
                'steps' => [
                    ['title' => 'Periksa Status Printer', 'body' => 'Pastikan printer menyala dan tidak menampilkan peringatan kertas atau tinta.'],
                    ['title' => 'Periksa Antrean Cetak', 'body' => 'Pastikan printer yang dipilih benar dan tidak sedang dijeda.'],
                    ['title' => 'Gunakan Panduan Resmi', 'body' => 'Minta bantuan teknisi untuk driver atau konfigurasi IP printer yang sesuai.'],
                ],
            ],
            'general' => [
                'text' => 'Terima kasih. Saya mencatat pertanyaan Anda dalam percakapan demo ini. Belum ada AI langsung atau pemeriksaan sistem. Anda bisa menjelaskan pesan error, perangkat atau aplikasi yang terdampak, dan langkah yang sudah dicoba.',
                'tag' => 'Bantuan Umum', 'guide' => 'Informasi untuk Tim IT:',
                'steps' => [
                    ['title' => 'Jelaskan Kendala Utama', 'body' => 'Sebutkan aplikasi atau perangkat dan pesan error yang terlihat.'],
                    ['title' => 'Cantumkan Waktu & Dampak', 'body' => 'Jelaskan sejak kapan kendala terjadi dan apakah pekerjaan terhenti.'],
                    ['title' => 'Lanjutkan Melalui Tiket', 'body' => 'Gunakan Buat Tiket Baru jika memerlukan penanganan teknisi. Pengiriman tiket saat ini juga masih simulasi.'],
                ],
            ],
        ];

        $assistantMessage = fn ($id, $topic, $time) => array_merge($replies[$topic], ['id' => $id, 'role' => 'assistant', 'time' => $time, 'topic' => $topic]);
        $sessions = [
            [
                'id' => 'vpn-demo', 'title' => 'Troubleshoot Koneksi VPN', 'icon' => 'link', 'status' => 'Sedang aktif', 'time' => '10:42 AM', 'date' => 'Sesi contoh · 24 Mei 2024',
                'messages' => [
                    ['id' => 'vpn-welcome', 'role' => 'assistant', 'text' => $welcome, 'time' => '10:40 AM', 'welcome' => true],
                    ['id' => 'vpn-question', 'role' => 'user', 'text' => 'VPN GlobalProtect saya selalu gagal menyambung (error: Connection Timed Out 504) sejak update Windows tadi pagi. Saya sedang remote di rumah dan perlu akses database staging.', 'time' => '10:41 AM'],
                    $assistantMessage('vpn-answer', 'vpn', '10:42 AM'),
                ],
            ],
            [
                'id' => 'password-demo', 'title' => 'Panduan Reset Password SSO', 'icon' => 'history', 'status' => 'Terselesaikan', 'time' => 'Kemarin', 'date' => 'Sesi contoh · 23 Mei 2024',
                'messages' => [
                    ['id' => 'password-question', 'role' => 'user', 'text' => 'Bagaimana cara reset password akun SSO perusahaan?', 'time' => '09:10 AM'],
                    $assistantMessage('password-answer', 'password', '09:11 AM'),
                    ['id' => 'password-thanks', 'role' => 'user', 'text' => 'Panduan pemulihannya sudah jelas. Terima kasih!', 'time' => '09:14 AM'],
                ],
            ],
            [
                'id' => 'software-demo', 'title' => 'Permintaan Akses Software', 'icon' => 'user-key', 'status' => 'Tiket #IT-8842', 'time' => '12 Apr', 'date' => 'Sesi contoh · 12 April 2024',
                'messages' => [
                    ['id' => 'software-question', 'role' => 'user', 'text' => 'Saya perlu lisensi Figma untuk tim Product Design. Apa yang harus disiapkan?', 'time' => '11:20 AM'],
                    $assistantMessage('software-answer', 'software', '11:21 AM'),
                ],
            ],
            [
                'id' => 'printer-demo', 'title' => 'Instalasi Driver Printer Kantor', 'icon' => 'printer', 'status' => 'Terselesaikan', 'time' => '08 Apr', 'date' => 'Sesi contoh · 8 April 2024',
                'messages' => [
                    ['id' => 'printer-question', 'role' => 'user', 'text' => 'Printer jaringan lantai 3 tidak muncul di laptop saya.', 'time' => '02:30 PM'],
                    $assistantMessage('printer-answer', 'printer', '02:31 PM'),
                ],
            ],
        ];

        return view('user.chat', [
            'user' => $user,
            'sessions' => $sessions,
            'replies' => $replies,
            'welcome' => $welcome,
            'recommendations' => ['Laptop tidak bisa connect WiFi kantor', 'Outlook sync error', 'Minta lisensi software baru'],
            'categories' => [
                ['title' => 'Hardware & Perangkat', 'description' => 'Monitor, baterai, docking station', 'icon' => 'monitor', 'tone' => 'amber', 'prompt' => 'Laptop dan monitor saya bermasalah. Apa yang perlu diperiksa?'],
                ['title' => 'Permintaan Software', 'description' => 'Lisensi Figma, Office, IDE', 'icon' => 'apps', 'tone' => 'blue', 'prompt' => 'Bagaimana cara meminta lisensi software baru?'],
                ['title' => 'Kendala Akun & Akses', 'description' => 'MFA reset, izin akses folder', 'icon' => 'user-key', 'tone' => 'mint', 'prompt' => 'Saya terkendala login SSO dan verifikasi MFA. Apa langkah awalnya?'],
            ],
        ]);
    }
}
