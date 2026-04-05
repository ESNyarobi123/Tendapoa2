<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Job extends Model
{
    use HasFactory;

    // Jedwali halisi (epuka mgongano na queue 'jobs')
    protected $table = 'work_orders';

    /** Ruhusu mass-assignment kwa field muhimu */
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'title_sw',
        'title_en',
        'description',
        'description_sw',
        'description_en',
        'image',
        'price',
        'lat',
        'lng',
        'address_text',
        'status',
        'accepted_worker_id',
        'selected_worker_id',
        'published_at',
        'completion_code',
        'completed_at',
        'poster_type',
        'posting_fee',
        'mfanyakazi_response',
        'worker_response',
        'assignee_response',
        'budget',
        'payout',
        'location',
        // New workflow fields
        'agreed_amount',
        'escrow_amount',
        'platform_fee_amount',
        'release_amount',
        'funded_payment_id',
        'funded_at',
        'accepted_by_worker_at',
        'submitted_at',
        'confirmed_at',
        'cancelled_at',
        'disputed_at',
        'auto_release_at',
        'urgency',
        'cancel_reason',
        'application_count',
    ];

    /** Hide raw locale columns from API/JSON; API gets single "title"/"description" from accessors */
    protected $hidden = [
        'title_sw',
        'title_en',
        'description_sw',
        'description_en',
    ];

    /** Casts/Types */
    protected $casts = [
        'published_at' => 'datetime',
        'completed_at' => 'datetime',
        'funded_at' => 'datetime',
        'accepted_by_worker_at' => 'datetime',
        'submitted_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'disputed_at' => 'datetime',
        'auto_release_at' => 'datetime',
        'price' => 'integer',
        'budget' => 'integer',
        'payout' => 'integer',
        'agreed_amount' => 'integer',
        'escrow_amount' => 'integer',
        'platform_fee_amount' => 'integer',
        'release_amount' => 'integer',
        'application_count' => 'integer',
        'posting_fee' => 'decimal:2',
        'lat' => 'float',
        'lng' => 'float',
    ];

    /** Appended attributes */
    protected $appends = ['image_url'];

    /** STATUS constants — NEW WORKFLOW */
    public const S_OPEN = 'open';

    public const S_AWAITING_PAYMENT = 'awaiting_payment';

    public const S_FUNDED = 'funded';

    public const S_IN_PROGRESS = 'in_progress';

    public const S_SUBMITTED = 'submitted';

    public const S_COMPLETED = 'completed';

    public const S_CANCELLED = 'cancelled';

    public const S_EXPIRED = 'expired';

    public const S_DISPUTED = 'disputed';

    public const S_REFUNDED = 'refunded';

    // Legacy compat
    public const S_OFFERED = 'offered';

    public const S_ASSIGNED = 'assigned';

    public const S_READY_FOR_CONFIRMATION = 'ready_for_confirmation';

    /** Valid state transitions: from => [allowed targets] */
    public const TRANSITIONS = [
        self::S_OPEN => [self::S_AWAITING_PAYMENT, self::S_CANCELLED, self::S_EXPIRED],
        self::S_AWAITING_PAYMENT => [self::S_FUNDED, self::S_OPEN, self::S_CANCELLED],
        self::S_FUNDED => [self::S_IN_PROGRESS, self::S_OPEN, self::S_CANCELLED, self::S_DISPUTED],
        self::S_IN_PROGRESS => [self::S_SUBMITTED, self::S_DISPUTED, self::S_CANCELLED],
        self::S_SUBMITTED => [self::S_COMPLETED, self::S_IN_PROGRESS, self::S_DISPUTED],
        self::S_DISPUTED => [self::S_COMPLETED, self::S_REFUNDED],
        self::S_COMPLETED => [],
        self::S_CANCELLED => [],
        self::S_EXPIRED => [],
        self::S_REFUNDED => [],
        // Legacy statuses can transition to new ones
        'pending_payment' => ['posted', self::S_OPEN, self::S_CANCELLED],
        'posted' => [self::S_OPEN, 'assigned', self::S_AWAITING_PAYMENT, self::S_CANCELLED],
        'assigned' => [self::S_IN_PROGRESS, self::S_CANCELLED],
        'ready_for_confirmation' => [self::S_COMPLETED, self::S_SUBMITTED],
    ];

    /**
     * Check if a status transition is valid.
     */
    public static function isValidTransition(string $from, string $to): bool
    {
        $allowed = self::TRANSITIONS[$from] ?? null;
        if ($allowed === null) {
            return true;
        } // Unknown source status — allow (legacy)

        return in_array($to, $allowed);
    }

    /* ===========================
     |  Mahusiano
     * =========================== */
    public function muhitaji()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Standard relationship for the job owner
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function acceptedWorker()
    {
        return $this->belongsTo(User::class, 'accepted_worker_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // imerudi kwenye jedwali jipya work_order_comments
    public function comments()
    {
        return $this->hasMany(JobComment::class, 'work_order_id')->latest();
    }

    // payments.work_order_id
    public function payment()
    {
        return $this->hasOne(Payment::class, 'work_order_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'work_order_id');
    }

    // private messages for this job
    public function privateMessages()
    {
        return $this->hasMany(PrivateMessage::class, 'work_order_id')->latest();
    }

    // NEW WORKFLOW relationships
    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'work_order_id')->latest();
    }

    public function selectedWorker()
    {
        return $this->belongsTo(User::class, 'selected_worker_id');
    }

    public function escrowEntries()
    {
        return $this->hasMany(EscrowLedger::class, 'work_order_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(JobStatusLog::class, 'work_order_id')->orderBy('created_at');
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class, 'work_order_id');
    }

    public function activeDispute()
    {
        return $this->hasOne(Dispute::class, 'work_order_id')
            ->whereIn('status', [Dispute::STATUS_OPEN, Dispute::STATUS_UNDER_REVIEW])
            ->latest();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'work_order_id');
    }

    /* ===========================
     |  Scopes zinazosaidia Controllers
     * =========================== */
    // Kazi zilizo "hai" kwa mfanyakazi husika
    public function scopeActiveForMfanyakazi($q, int $mfanyakaziId)
    {
        return $q->where('accepted_worker_id', $mfanyakaziId)
            ->whereIn('status', [self::S_FUNDED, self::S_IN_PROGRESS, self::S_SUBMITTED, self::S_ASSIGNED, self::S_READY_FOR_CONFIRMATION]);
    }

    public function scopeAssignedTo($q, int $mfanyakaziId)
    {
        return $q->where('accepted_worker_id', $mfanyakaziId);
    }

    // Kazi zilizo wazi kwa wafanyakazi (feed)
    public function scopeOpen($q)
    {
        return $q->where('status', self::S_OPEN);
    }

    // Kazi za muhitaji ambazo zinahitaji hatua
    public function scopeNeedsClientAction($q, int $clientId)
    {
        return $q->where('user_id', $clientId)
            ->whereIn('status', [self::S_OPEN, self::S_AWAITING_PAYMENT, self::S_SUBMITTED]);
    }

    /* ===========================
     |  Status transition helper
     * =========================== */
    public function transitionStatus(string $newStatus, ?int $userId = null, ?string $note = null, array $meta = [], bool $force = false): self
    {
        $oldStatus = $this->status;

        if (! $force && ! self::isValidTransition($oldStatus, $newStatus)) {
            \Log::warning("Invalid job transition blocked: #{$this->id} {$oldStatus} → {$newStatus}", [
                'user_id' => $userId,
                'note' => $note,
            ]);
            throw new \RuntimeException("Mpito wa hali haurauhusiwi: {$oldStatus} → {$newStatus}");
        }

        JobStatusLog::log($this, $newStatus, $userId, $note, $meta);
        $this->status = $newStatus;
        $this->save();

        return $this;
    }

    /* ===========================
     |  Helpers / Accessors
     * =========================== */

    /**
     * Localized title: API returns single "title" based on Accept-Language.
     * Fallback: if requested locale column is empty (Groq failed/delayed), return the other language
     * so the user never sees blank — better Kiswahili than empty.
     */
    public function getTitleAttribute($value): string
    {
        $locale = app()->getLocale();
        $sw = array_key_exists('title_sw', $this->attributes) ? $this->attributes['title_sw'] : null;
        $en = array_key_exists('title_en', $this->attributes) ? $this->attributes['title_en'] : null;
        $sw = $sw !== null && (string) $sw !== '' ? (string) $sw : null;
        $en = $en !== null && (string) $en !== '' ? (string) $en : null;

        if ($locale === 'sw') {
            return $sw ?? $en ?? (string) ($value ?? '');
        }

        // Accept-Language: en — prefer title_en; if empty (AI failed), fallback to title_sw
        return $en ?? $sw ?? (string) ($value ?? '');
    }

    /**
     * Localized description. Same fallback: if requested locale empty, use the other language.
     */
    public function getDescriptionAttribute($value): ?string
    {
        $locale = app()->getLocale();
        $sw = array_key_exists('description_sw', $this->attributes) ? $this->attributes['description_sw'] : null;
        $en = array_key_exists('description_en', $this->attributes) ? $this->attributes['description_en'] : null;
        $sw = $sw !== null && (string) $sw !== '' ? $sw : null;
        $en = $en !== null && (string) $en !== '' ? $en : null;

        if ($locale === 'sw') {
            return $sw ?? $en ?? $value;
        }

        return $en ?? $sw ?? $value;
    }

    // Amount inayopatikana (priority: payout -> price -> budget)
    public function getAmountAttribute(): int
    {
        return (int) ($this->payout ?? $this->price ?? $this->budget ?? 0);
    }

    // Soma response bila kujali jina la column (mfanyakazi_response > worker_response > assignee_response)
    public function getMfanyakaziResponseAttribute($value)
    {
        if (! is_null($value)) {
            return $value;
        }

        // Ikiwa column ya juu haipo kwenye schema/record, jaribu nyingine
        if (array_key_exists('worker_response', $this->attributes) && $this->attributes['worker_response'] !== null) {
            return $this->attributes['worker_response'];
        }
        if (array_key_exists('assignee_response', $this->attributes) && $this->attributes['assignee_response'] !== null) {
            return $this->attributes['assignee_response'];
        }

        return null;
    }

    // Andika response kwenye column yoyote iliyopo (kipaumbele: mfanyakazi_response > worker_response > assignee_response)
    public function setMfanyakaziResponseAttribute($value): void
    {
        // Ikiwa kolamu ipo kabisa kwenye DB, andika humo; la sivyo tunafall-back kwenye nyingine
        if (Schema::hasColumn($this->table, 'mfanyakazi_response')) {
            $this->attributes['mfanyakazi_response'] = $value;

            return;
        }
        if (Schema::hasColumn($this->table, 'worker_response')) {
            $this->attributes['worker_response'] = $value;

            return;
        }
        if (Schema::hasColumn($this->table, 'assignee_response')) {
            $this->attributes['assignee_response'] = $value;

            return;
        }
        // Hakuna kolamu yoyote—tulia kimya (usi-sabotage save())
    }

    // Helper: jina la kolamu ya response itakayotumika (kwa Controllers ikiwa watataka)
    public static function responseColumn(): ?string
    {
        $table = (new static)->getTable();
        if (Schema::hasColumn($table, 'mfanyakazi_response')) {
            return 'mfanyakazi_response';
        }
        if (Schema::hasColumn($table, 'worker_response')) {
            return 'worker_response';
        }
        if (Schema::hasColumn($table, 'assignee_response')) {
            return 'assignee_response';
        }

        return null;
    }

    /**
     * Get image URL attribute
     * More robust implementation that checks multiple locations and handles edge cases
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        // Check if file exists in the private/root storage
        if (Storage::disk('public')->exists($this->image)) {
            // Get timestamp for cache busting
            $timestamp = filemtime(storage_path('app/public/'.$this->image));

            // Use the /image/ route which serves from storage_path()
            return url('image/'.$this->image).'?v='.$timestamp;
        }

        // Fallback: return the path served via route (in case check failed but file exists)
        return url('image/'.$this->image);
    }
}
