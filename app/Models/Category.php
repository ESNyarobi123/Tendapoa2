<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'name_sw', 'name_en', 'slug'];

    /** Hide raw locale columns; API gets single "name" from accessor */
    protected $hidden = ['name_sw', 'name_en'];

    /** Localized name: API returns single "name" based on Accept-Language */
    public function getNameAttribute($value): string
    {
        $locale = app()->getLocale();
        $sw = array_key_exists('name_sw', $this->attributes) ? $this->attributes['name_sw'] : null;
        $en = array_key_exists('name_en', $this->attributes) ? $this->attributes['name_en'] : null;
        if ($locale === 'sw' && (string)$sw !== '') {
            return (string) $sw;
        }
        if ($locale === 'en' && (string)$en !== '') {
            return (string) $en;
        }
        return (string) ($sw ?? $en ?? $value ?? '');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
}
