<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the job poster when the selected worker declines the job.
 */
class WorkerDeclinedJobNotification extends Notification
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
            'type' => 'worker_declined',
            'job_id' => $this->job->id,
            'worker_id' => $this->worker->id,
            'title' => 'Mfanyakazi amekataa kazi',
            'message' => "{$this->worker->name} amekataa kufanya kazi yako: \"{$this->job->title}\". Kazi imerudi kwenye soko, unaweza kuchagua mfanyakazi mwingine.",
            'action_url' => "/jobs/{$this->job->id}",
        ];
    }
}
