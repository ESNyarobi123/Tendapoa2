<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Negotiation extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'quote_id',
        'comment_id',
        'sender_id',
        'receiver_id',
        'message',
        'proposed_price',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'proposed_price' => 'integer',
    ];

    // Relationships
    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id', 'id');
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function comment()
    {
        return $this->belongsTo(JobComment::class, 'comment_id', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
