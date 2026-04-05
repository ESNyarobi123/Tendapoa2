<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'raised_by',
        'against_user',
        'status',
        'reason',
        'resolution_note',
        'worker_amount',
        'client_refund_amount',
        'resolved_by',
        'resolved_at',
        'meta',
    ];

    protected $casts = [
        'worker_amount' => 'integer',
        'client_refund_amount' => 'integer',
        'resolved_at' => 'datetime',
        'meta' => 'array',
    ];

    // Status constants
    const STATUS_OPEN = 'open';

    const STATUS_UNDER_REVIEW = 'under_review';

    const STATUS_RESOLVED_FULL_WORKER = 'resolved_full_worker';

    const STATUS_RESOLVED_FULL_CLIENT = 'resolved_full_client';

    const STATUS_RESOLVED_SPLIT = 'resolved_split';

    const STATUS_CLOSED = 'closed';

    /* ===========================
     |  Relationships
     * =========================== */

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'work_order_id');
    }

    public function raisedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function againstUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'against_user');
    }

    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DisputeMessage::class)->orderBy('created_at');
    }

    /* ===========================
     |  Helpers
     * =========================== */

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_UNDER_REVIEW]);
    }

    public function isResolved(): bool
    {
        return ! $this->isOpen() && $this->status !== self::STATUS_CLOSED;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'Wazi',
            self::STATUS_UNDER_REVIEW => 'Inaangaliwa',
            self::STATUS_RESOLVED_FULL_WORKER => 'Mfanyakazi Amelipwa Kamili',
            self::STATUS_RESOLVED_FULL_CLIENT => 'Muhitaji Amerudishiwa',
            self::STATUS_RESOLVED_SPLIT => 'Imegawanywa',
            self::STATUS_CLOSED => 'Imefungwa',
            default => 'Haijulikani',
        };
    }
}
