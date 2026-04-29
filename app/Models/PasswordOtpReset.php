<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordOtpReset extends Model
{
    /** Window after OTP verification within which the user must set a new password. */
    public const RESET_WINDOW_MINUTES = 15;

    /** Maximum incorrect OTP attempts before the OTP is invalidated. */
    public const MAX_ATTEMPTS = 5;

    protected $fillable = [
        'email',
        'otp',
        'reset_token',
        'verified_at',
        'attempts',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null && $this->reset_token !== null;
    }

    public function isResetWindowOpen(): bool
    {
        return $this->isVerified()
            && $this->verified_at->copy()->addMinutes(self::RESET_WINDOW_MINUTES)->isFuture();
    }

    public function hasExceededAttempts(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }
}
