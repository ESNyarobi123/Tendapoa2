<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkerAcceptedNotification extends Notification
{
    use Queueable;

    public $job;
    public $worker;

    /**
     * Create a new notification instance.
     */
    public function __construct($job, $worker)
    {
        $this->job = $job;
        $this->worker = $worker;
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
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'worker_accepted',
            'job_id' => $this->job->id,
            'title' => 'Mfanyakazi Amekubali Kazi',
            'message' => "Mfanyakazi {$this->worker->name} amekubali kufanya kazi yako: '{$this->job->title}'.",
            'action_url' => "/jobs/{$this->job->id}",
        ];
    }
}
