<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $fillable = [
        'version',
        'file_path',
        'file_name',
        'file_size',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
    ];

    /**
     * Get the active version
     */
    public static function getActive()
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Get the latest version (by created_at)
     */
    public static function getLatest()
    {
        return static::latest()->first();
    }

    /**
     * Scope to get active versions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
