<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the job poster when the job is confirmed completed (escrow released).
 */
class JobCompletedForClientNotification extends Notification
{
    use Queueable;

    public function __construct(public $job, public $worker)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'fcm'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'job_completed_client',
            'job_id' => $this->job->id,
            'worker_id' => $this->worker?->id,
            'title' => 'Kazi yako imekamilika ✅',
            'message' => "Kazi yako \"{$this->job->title}\" imekamilika. ".($this->worker ? "Tafadhali toa review kwa {$this->worker->name}." : 'Tafadhali toa review.'),
            'action_url' => "/jobs/{$this->job->id}",
        ];
    }
}
