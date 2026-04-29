<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the worker when their application is rejected (or another worker selected).
 */
class ApplicationRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public $job, public ?string $reason = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'fcm'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_rejected',
            'job_id' => $this->job->id,
            'title' => 'Ombi lako halikukubaliwa',
            'message' => "Samahani, ombi lako kwa kazi \"{$this->job->title}\" halikuchaguliwa wakati huu. Endelea kutafuta kazi nyingine.",
            'reason' => $this->reason,
            'action_url' => "/jobs/{$this->job->id}",
        ];
    }
}
