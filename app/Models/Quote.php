<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    protected $fillable = [
        'job_id',
        'worker_id',
        'quoted_price',
        'eta_minutes',
        'notes',
        'status',
        'expires_at',
        'selected_at',
        'confirmed_at',
    ];

    protected $casts = [
        'quoted_price' => 'integer',
        'eta_minutes' => 'integer',
        'expires_at' => 'datetime',
        'selected_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    // Relationships
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    // Helper methods
    public function isExpired(): bool
    {
        return $this->expires_at && now()->isAfter($this->expires_at);
    }

    public function isSelected(): bool
    {
        return $this->status === 'selected';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmedPrice(): bool
    {
        return $this->status === 'confirmed_price';
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    public function markAsSelected(): void
    {
        $this->update([
            'status' => 'selected',
            'selected_at' => now(),
        ]);
    }

    public function markAsRejected(): void
    {
        $this->update(['status' => 'rejected']);
    }
}
