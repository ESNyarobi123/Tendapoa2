<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    use HasFactory;

    protected $table = 'job_applications';

    protected $fillable = [
        'work_order_id',
        'worker_id',
        'proposed_amount',
        'message',
        'eta_text',
        'eta_minutes',
        'status',
        'counter_amount',
        'client_response_note',
        'shortlisted_at',
        'selected_at',
        'rejected_at',
        'withdrawn_at',
        'countered_at',
    ];

    protected $casts = [
        'proposed_amount' => 'integer',
        'counter_amount' => 'integer',
        'eta_minutes' => 'integer',
        'shortlisted_at' => 'datetime',
        'selected_at' => 'datetime',
        'rejected_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'countered_at' => 'datetime',
    ];

    // Status constants
    const STATUS_APPLIED = 'applied';

    const STATUS_SHORTLISTED = 'shortlisted';

    const STATUS_REJECTED = 'rejected';

    const STATUS_SELECTED = 'selected';

    const STATUS_WITHDRAWN = 'withdrawn';

    const STATUS_COUNTERED = 'countered';

    const STATUS_ACCEPTED_COUNTER = 'accepted_counter';

    /* ===========================
     |  Relationships
     * =========================== */

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'work_order_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    /* ===========================
     |  Scopes
     * =========================== */

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_WITHDRAWN, self::STATUS_REJECTED]);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_APPLIED);
    }

    public function scopeShortlisted($query)
    {
        return $query->where('status', self::STATUS_SHORTLISTED);
    }

    public function scopeForJob($query, int $jobId)
    {
        return $query->where('work_order_id', $jobId);
    }

    /* ===========================
     |  Helpers
     * =========================== */

    public function isActive(): bool
    {
        return ! in_array($this->status, [self::STATUS_WITHDRAWN, self::STATUS_REJECTED]);
    }

    public function isSelected(): bool
    {
        return $this->status === self::STATUS_SELECTED;
    }

    public function isCountered(): bool
    {
        return $this->status === self::STATUS_COUNTERED;
    }

    public function getAgreedAmount(): int
    {
        // If client countered and worker accepted, use counter_amount
        if ($this->status === self::STATUS_ACCEPTED_COUNTER && $this->counter_amount) {
            return $this->counter_amount;
        }

        // If selected directly, use proposed_amount
        return $this->proposed_amount;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_APPLIED => 'Imewasilishwa',
            self::STATUS_SHORTLISTED => 'Imeorodheshwa',
            self::STATUS_REJECTED => 'Imekataliwa',
            self::STATUS_SELECTED => 'Imechaguliwa',
            self::STATUS_WITHDRAWN => 'Imeondolewa',
            self::STATUS_COUNTERED => 'Counter Offer',
            self::STATUS_ACCEPTED_COUNTER => 'Counter Imekubaliwa',
            default => 'Haijulikani',
        };
    }
}
