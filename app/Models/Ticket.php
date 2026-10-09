<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    public const CATEGORIES = ['HRM', 'Healthcare', 'Billing', 'Technical'];
    public const PRIORITIES = ['low', 'medium', 'high', 'critical'];
    public const STATUSES = ['open', 'in_progress', 'resolved', 'closed'];

    protected $fillable = [
        'subject', 'description', 'category', 'priority', 'status',
        'customer_id', 'agent_id', 'due_at', 'escalation_level', 'resolved_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->oldest();
    }

    // Only return the tickets this user is allowed to see
    public function scopeVisibleTo($query, User $user)
    {
        return match ($user->role) {
            'super_admin' => $query,
            'agent' => $query->where('agent_id', $user->id),
            default => $query->where('customer_id', $user->id),
        };
    }

    // $ticket->code  gives  TCK-00042
    public function getCodeAttribute(): string
    {
        return 'TCK-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['resolved', 'closed']);
    }

    public function isBreached(): bool
    {
        return ! $this->isClosed() && $this->due_at->isPast();
    }

    public function priorityColor(): string
    {
        return ['critical' => 'red', 'high' => 'amber', 'medium' => 'blue', 'low' => 'gray'][$this->priority];
    }

    public function statusColor(): string
    {
        return ['open' => 'blue', 'in_progress' => 'amber', 'resolved' => 'green', 'closed' => 'gray'][$this->status];
    }
}