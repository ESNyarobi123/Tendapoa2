<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobAvailableNotification extends Notification
{
    use Queueable;

    public $job;
    public $distance;
    public $distanceLabel;

    /**
     * Create a new notification instance.
     */
    public function __construct($job, $distance, $distanceLabel)
    {
        $this->job = $job;
        $this->distance = $distance; // in km
        $this->distanceLabel = $distanceLabel; // e.g. "Karibu", "Wastani", "Mbali"
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
        return [
            'type' => 'job_available',
            'job_id' => $this->job->id,
            'title' => 'Kazi Mpya Imeingia!',
            'message' => "Kuna kazi mpya ya {$this->job->title} umbali wa {$this->distanceLabel} (" . round($this->distance, 1) . "km) kutoka ulipo.",
            'distance_label' => $this->distanceLabel,
            'distance' => $this->distance,
            'action_url' => "/jobs/{$this->job->id}",
        ];
    }
}
