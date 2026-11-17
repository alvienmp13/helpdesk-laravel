<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    // Ownership, assignment, number, status and resolution are server-controlled.
    protected $fillable = ['title', 'description', 'category_id', 'priority', 'department', 'location'];

    protected function casts(): array
    {
        return ['status' => TicketStatus::class, 'resolved_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function messages(): HasMany { return $this->hasMany(TicketMessage::class)->orderBy('id'); }
    public function attachments(): HasMany { return $this->hasMany(TicketAttachment::class); }
    public function statusHistories(): HasMany { return $this->hasMany(TicketStatusHistory::class)->orderBy('id'); }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->role) {
            'admin' => $query,
            'agent' => $query->where('assigned_to', $user->id)->where('status', '!=', TicketStatus::Draft->value),
            'user' => $query->where('user_id', $user->id),
            default => $query->whereRaw('1 = 0'),
        };
    }
}
