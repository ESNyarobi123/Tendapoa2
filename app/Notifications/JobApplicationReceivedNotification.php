<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the job poster (muhitaji or mfanyakazi who posted) when someone applies/comments.
 */
class JobApplicationReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(public $job, public $worker, public ?int $bidAmount = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'fcm'];
    }

    public function toArray(object $notifiable): array
    {
        $bidNote = $this->bidAmount ? ' Bei iliyopendekezwa: TZS '.number_format($this->bidAmount).'.' : '';
        return [
            'type' => 'job_application_received',
            'job_id' => $this->job->id,
            'worker_id' => $this->worker->id,
            'title' => 'Ombi jipya la kufanya kazi',
            'message' => "{$this->worker->name} ameomba kufanya kazi yako: \"{$this->job->title}\".".$bidNote,
            'action_url' => "/jobs/{$this->job->id}",
        ];
    }
}
