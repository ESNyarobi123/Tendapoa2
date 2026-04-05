<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobStatusLog extends Model
{
    use HasFactory;

    protected $table = 'job_status_logs';

    protected $fillable = [
        'work_order_id',
        'user_id',
        'from_status',
        'to_status',
        'note',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'work_order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quick factory method to log a status change.
     */
    public static function log(Job $job, string $toStatus, ?int $userId = null, ?string $note = null, array $meta = []): self
    {
        return static::create([
            'work_order_id' => $job->id,
            'user_id' => $userId,
            'from_status' => $job->status,
            'to_status' => $toStatus,
            'note' => $note,
            'meta' => $meta ?: null,
        ]);
    }
}
