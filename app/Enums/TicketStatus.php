<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case InProgress = 'in_progress';
    case WaitingUser = 'waiting_user';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft', self::Open => 'Terbuka', self::InProgress => 'Sedang Diproses',
            self::WaitingUser => 'Menunggu Respon Anda', self::Resolved => 'Selesai', self::Closed => 'Ditutup',
        };
    }

    public function next(): array
    {
        return match ($this) {
            self::Draft => [self::Open],
            self::Open => [self::InProgress],
            self::InProgress => [self::WaitingUser, self::Resolved],
            self::WaitingUser => [self::InProgress, self::Resolved],
            self::Resolved => [self::InProgress, self::Closed],
            self::Closed => [],
        };
    }
}
