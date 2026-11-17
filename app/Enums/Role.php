<?php

namespace App\Enums;

enum Role: string
{
    case User = 'user';
    case Agent = 'agent';
    case Admin = 'admin';

    public function dashboardRoute(): string
    {
        return $this->value.'.dashboard';
    }

    public function label(): string
    {
        return match ($this) {
            self::User => 'Employee / End User',
            self::Agent => 'IT Support Agent',
            self::Admin => 'Administrator',
        };
    }
}
