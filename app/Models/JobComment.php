<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobComment extends Model
{
    use HasFactory;

    protected $table = 'work_order_comments';

    protected $fillable = [
        'work_order_id',
        'user_id',
        'parent_id',
        'message',
        'type',
        'status',
        'is_application',
        'is_negotiation',
        'bid_amount',
        'original_price',
        'counter_amount',
        'reply_message',
        'replied_at',
    ];

    protected $casts = [
        'is_application' => 'boolean',
        'is_negotiation' => 'boolean',
        'replied_at' => 'datetime',
        'bid_amount' => 'integer',
        'original_price' => 'integer',
        'counter_amount' => 'integer',
    ];

    // Constants for types
    const TYPE_COMMENT = 'comment';
    const TYPE_APPLICATION = 'application';
    const TYPE_OFFER = 'offer';
    const TYPE_COUNTER_OFFER = 'counter_offer';
    const TYPE_REPLY = 'reply';
    const TYPE_SYSTEM = 'system';

    // Constants for status
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COUNTERED = 'countered';

    /**
     * Get the job this comment belongs to
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'work_order_id');
    }

    /**
     * Get the user who made this comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment (for replies)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(JobComment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment
     */
    public function replies(): HasMany
    {
        return $this->hasMany(JobComment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    /**
     * Check if this is an application
     */
    public function isApplication(): bool
    {
        return $this->is_application || $this->type === self::TYPE_APPLICATION;
    }

    /**
     * Check if this is a price negotiation/offer
     */
    public function isOffer(): bool
    {
        return $this->is_negotiation || $this->type === self::TYPE_OFFER;
    }

    /**
     * Check if this comment has been responded to
     */
    public function hasReply(): bool
    {
        return !empty($this->reply_message) || $this->replied_at !== null;
    }

    /**
     * Check if this is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if this was accepted
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Check if this was rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if this was countered
     */
    public function isCountered(): bool
    {
        return $this->status === self::STATUS_COUNTERED;
    }

    /**
     * Get the final agreed price
     */
    public function getAgreedPrice(): ?int
    {
        if ($this->isAccepted()) {
            // If countered and accepted, use counter amount
            if ($this->counter_amount) {
                return $this->counter_amount;
            }
            // Otherwise use bid amount
            return $this->bid_amount;
        }
        return null;
    }

    /**
     * Scope for applications only
     */
    public function scopeApplications($query)
    {
        return $query->where(function ($q) {
            $q->where('is_application', true)
                ->orWhere('type', self::TYPE_APPLICATION);
        });
    }

    /**
     * Scope for offers/negotiations only
     */
    public function scopeOffers($query)
    {
        return $query->where(function ($q) {
            $q->where('is_negotiation', true)
                ->orWhere('type', self::TYPE_OFFER);
        });
    }

    /**
     * Scope for pending items
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for top-level comments (not replies)
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get status label in Swahili
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Inasubiri',
            self::STATUS_ACCEPTED => 'Imekubaliwa',
            self::STATUS_REJECTED => 'Imekataliwa',
            self::STATUS_COUNTERED => 'Imepewa Counter',
            default => 'Haijulikani'
        };
    }

    /**
     * Get type label in Swahili
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_COMMENT => 'Maoni',
            self::TYPE_APPLICATION => 'Maombi',
            self::TYPE_OFFER => 'Pendekezo la Bei',
            self::TYPE_COUNTER_OFFER => 'Counter Offer',
            self::TYPE_REPLY => 'Jibu',
            self::TYPE_SYSTEM => 'System',
            default => 'Maoni'
        };
    }
}
