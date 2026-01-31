<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens; // <--- Hakikisha hii ipo

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // <--- Ongeza HasApiTokens hapa

    protected $fillable = ['name', 'email', 'password', 'phone', 'role', 'lat', 'lng', 'is_active', 'profile_photo_path'];
    protected $hidden = ['password', 'remember_token'];
    protected $appends = ['profile_photo_url'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path) {
            // Check if file exists in the private/root storage
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->profile_photo_path)) {
                // Get timestamp for cache busting
                $timestamp = filemtime(storage_path('app/public/' . $this->profile_photo_path));
                // Use the /image/ route which serves from storage_path()
                return url('image/' . $this->profile_photo_path) . '?v=' . $timestamp;
            }
        }

        // Default avatar
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name ?? 'User') . '&color=7F9CF5&background=EBF4FF';
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'user_id');
    } // as muhitaji
    public function assignedJobs()
    {
        return $this->hasMany(Job::class, 'accepted_worker_id');
    }
    public function comments()
    {
        return $this->hasMany(JobComment::class);
    }
    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }
    public function sentMessages()
    {
        return $this->hasMany(PrivateMessage::class, 'sender_id');
    }
    public function receivedMessages()
    {
        return $this->hasMany(PrivateMessage::class, 'receiver_id');
    }

    // Additional relationships for admin
    public function muhitaji()
    {
        return $this->hasMany(Job::class, 'user_id');
    }
    public function mfanyakazi()
    {
        return $this->hasMany(Job::class, 'accepted_worker_id');
    }

    // Wallet
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
    public function ensureWallet(): Wallet
    {
        return $this->wallet()->firstOrCreate([], ['balance' => 0]);
    }

    // Location helpers
    public function hasLocation(): bool
    {
        return !is_null($this->lat) && !is_null($this->lng);
    }

    public function getLocationString(): ?string
    {
        if (!$this->hasLocation()) {
            return null;
        }
        return "{$this->lat}, {$this->lng}";
    }
}
