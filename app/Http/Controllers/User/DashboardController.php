<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Support\TicketPresenter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        Gate::authorize('viewAny', Ticket::class);
        $base = Ticket::visibleTo($request->user());
        $counts = (clone $base)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $activeCount = $counts->get('open', 0) + $counts->get('in_progress', 0) + $counts->get('waiting_user', 0);
        return view('user.dashboard', [
            'user' => $request->user()->portalIdentity(),
            'services' => [
                [
                    'title' => 'Buat Tiket Baru',
                    'description' => 'Laporkan kendala hardware, software crash, atau ajukan permintaan inventaris IT resmi.',
                    'icon' => 'ticket-plus', 'badge' => 'UTAMA', 'tone' => 'primary',
                    'action' => 'Buka Formulir Tiket', 'target' => 'create-ticket', 'action_icon' => 'arrow-right',
                ],
                [
                    'title' => 'Chat dengan AI Support',
                    'description' => 'Dapatkan pemecahan masalah otomatis secara instan tanpa perlu antre menunggu teknisi meja bantuan.',
                    'icon' => 'bot', 'badge' => 'Respon Cepat', 'tone' => 'mint',
                    'action' => 'Mulai Obrolan AI', 'target' => 'ai', 'action_icon' => 'chat',
                ],
                [
                    'title' => 'Jelajahi Solusi / FAQ',
                    'description' => 'Kumpulan panduan teknis langkah demi langkah untuk VPN, konfigurasi printer, & reset password mandiri.',
                    'icon' => 'book', 'badge' => 'Dokumentasi', 'tone' => 'blue',
                    'action' => 'Buka Knowledge Base', 'target' => 'knowledge', 'action_icon' => 'arrow-up-right',
                ],
            ],
            'statistics' => [
                ['label' => 'Total Tiket', 'value' => $counts->sum(), 'description' => 'Seluruh tiket termasuk draft', 'icon' => 'ticket', 'tone' => 'slate'],
                ['label' => 'Sedang Diproses', 'value' => $counts->get('in_progress', 0), 'description' => 'Dalam penanganan teknisi', 'icon' => 'headset', 'tone' => 'blue'],
                ['label' => 'Menunggu Respon', 'value' => $counts->get('waiting_user', 0), 'description' => 'Perlu tindakan dari Anda', 'icon' => 'clipboard-clock', 'tone' => 'amber'],
                ['label' => 'Tiket Selesai', 'value' => $counts->get('resolved', 0) + $counts->get('closed', 0), 'description' => 'Selesai atau ditutup', 'icon' => 'check-circle', 'tone' => 'mint'],
            ],
            'activeCount' => $activeCount,
            'tickets' => (clone $base)->with(['category', 'assignee'])->whereIn('status', ['open', 'in_progress', 'waiting_user'])->latest('id')->limit(5)->get()->map(TicketPresenter::card(...))->all(),
            'devices' => [
                ['name' => 'MacBook Pro 14” M2', 'detail' => 'Asset Tag: #IT-MBP-8812', 'icon' => 'laptop', 'tone' => 'blue'],
                ['name' => 'YubiKey 5 NFC', 'detail' => '2FA Hardware Key • Aktif', 'icon' => 'shield', 'tone' => 'mint'],
            ],
            'prompts' => [
                ['label' => 'Cara hubungkan VPN kantor', 'icon' => 'link', 'key' => 'vpn'],
                ['label' => 'Reset kata sandi email SSO', 'icon' => 'key', 'key' => 'password'],
                ['label' => 'Request software baru', 'icon' => 'puzzle', 'key' => 'software'],
            ],
        ]);
    }
}
