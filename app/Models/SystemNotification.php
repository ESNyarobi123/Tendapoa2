<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    //
    protected $fillable = [
        'title',
        'message',
        'target',
        'action_url',
        'sent_by',
        'total_count',
        'sent_count',
        'failed_count',
        'fcm_sent_count',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
