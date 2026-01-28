<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobStatusNotification extends Notification
{
    use Queueable;

    public $job;
    public $status;
    public $customMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct($job, $status, $customMessage = null)
    {
        $this->job = $job;
        $this->status = $status;
        $this->customMessage = $customMessage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = 'Hali ya Kazi Imebadilika';
        $message = "Hali ya kazi yako '{$this->job->title}' imebadilika na kuwa: {$this->status}.";

        if ($this->status == 'posted') {
            $title = 'Kazi Imepostiwa';
            $message = "Kazi yako '{$this->job->title}' imepostiwa kikamilifu.";
        } elseif ($this->status == 'pending') {
            $title = 'Kazi Inasubiri';
            $message = "Kazi yako '{$this->job->title}' inaendelea kusubiri (pending).";
        } elseif ($this->status == 'cancelled') {
            $title = 'Kazi Imefutwa';
            $message = "Kazi yako '{$this->job->title}' imefutwa/cancel.";
        } elseif ($this->status == 'deleted') {
            $title = 'Kazi Imeondolewa';
            $message = "Kazi yako '{$this->job->title}' imeondolewa kwenye mfumo.";
        } else if ($this->status == 'failed') {
            $title = 'Imeshindikana Kuposti';
            $message = "Kazi yako '{$this->job->title}' imeshindikana kupostiwa.";
        }

        if ($this->customMessage) {
            $message = $this->customMessage;
        }

        return [
            'type' => 'job_status',
            'job_id' => $this->job->id,
            'title' => $title,
            'message' => $message,
            'status' => $this->status,
            'action_url' => "/jobs/{$this->job->id}",
        ];
    }
}
