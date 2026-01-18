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
        'sent_by'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
