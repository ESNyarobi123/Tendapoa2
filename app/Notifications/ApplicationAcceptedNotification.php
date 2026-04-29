<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the worker when their application is selected/accepted by the job poster.
 */
class ApplicationAcceptedNotification extends Notification
{
    use Queueable;

    public function __construct(public $job, public ?int $agreedPrice = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'fcm'];
    }

    public function toArray(object $notifiable): array
    {
        $price = $this->agreedPrice ?? $this->job->price;
        return [
            'type' => 'application_accepted',
            'job_id' => $this->job->id,
            'title' => 'Ombi lako limekubaliwa! 🎉',
            'message' => "Hongera! Umechaguliwa kufanya kazi: \"{$this->job->title}\". Bei: TZS ".number_format($price).'.',
            'agreed_price' => $price,
            'action_url' => "/jobs/{$this->job->id}",
        ];
    }
}
