<?php

namespace App\Models;

use App\Enums\Role;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // Role is deliberately excluded from Fillable. Only trusted server code sets it.
    protected $attributes = ['role' => 'user'];

    public function tickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function assignedTickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function dashboardRoute(): string
    {
        $role = Role::tryFrom($this->role ?? '');
        abort_unless($role, 403, 'Role akun tidak dikenali. Hubungi administrator.');

        return $role->dashboardRoute();
    }

    public function portalIdentity(): array
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name,
            'first_name' => explode(' ', trim($this->name))[0],
            'role' => Role::tryFrom($this->role ?? '')?->label() ?? 'Akses tidak tersedia',
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
