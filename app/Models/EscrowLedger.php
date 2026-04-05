<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscrowLedger extends Model
{
    use HasFactory;

    protected $table = 'escrow_ledger';

    protected $fillable = [
        'work_order_id',
        'client_id',
        'worker_id',
        'type',
        'amount',
        'description',
        'payment_id',
        'wallet_transaction_id',
        'meta',
    ];

    protected $casts = [
        'amount' => 'integer',
        'meta' => 'array',
    ];

    // Type constants
    const TYPE_HOLD = 'hold';

    const TYPE_RELEASE = 'release';

    const TYPE_REFUND = 'refund';

    const TYPE_PARTIAL_REFUND = 'partial_refund';

    const TYPE_PLATFORM_FEE = 'platform_fee';

    /* ===========================
     |  Relationships
     * =========================== */

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'work_order_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /* ===========================
     |  Scopes
     * =========================== */

    public function scopeForJob($query, int $jobId)
    {
        return $query->where('work_order_id', $jobId);
    }

    public function scopeHolds($query)
    {
        return $query->where('type', self::TYPE_HOLD);
    }

    public function scopeReleases($query)
    {
        return $query->where('type', self::TYPE_RELEASE);
    }
}
