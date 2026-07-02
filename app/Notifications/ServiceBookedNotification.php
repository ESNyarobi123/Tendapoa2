<?php

namespace App\Notifications;

use App\Models\Job;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the service provider when a client completes escrow for a service booking.
 */
class ServiceBookedNotification extends Notification
{
    use Queueable;

    public function __construct(public Job $job, public User $client)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'fcm'];
    }

    public function toFcm(object $notifiable): array
    {
        $payload = $this->toArray($notifiable);

        return [
            'type' => 'service_booked',
            'title' => $payload['title'],
            'body' => $payload['message'],
            'data' => $payload,
        ];
    }

    public function toArray(object $notifiable): array
    {
        $amount = (int) ($this->job->agreed_amount ?? $this->job->price);
        $title = $this->job->getAttributes()['title'] ?? $this->job->title;
        $message = "Umeajiriwa na {$this->client->name} kwa \"{$title}\".";

        return [
            'type' => 'service_booked',
            'job_id' => $this->job->id,
            'listing_id' => $this->job->source_listing_id,
            'client_id' => $this->client->id,
            'client_name' => $this->client->name,
            'title' => 'Umeajiriwa! 🎉',
            'message' => $message,
            'agreed_price' => $amount,
            'action_url' => "/jobs/{$this->job->id}",
            'engagement_type' => $this->job->engagement_type,
        ];
    }
}
