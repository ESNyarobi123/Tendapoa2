<?php

namespace App\Services;

use App\Models\{Job, User};
use App\Notifications\JobAvailableNotification;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Notify active workers within radius via Laravel database notifications (same labels as JobController).
     * Push/SMS: extend sendPushNotification / sendSMS when FCM/SMS is configured.
     *
     * @return array{notified:int, radius_km:float, candidate_count:int, skipped?:string}
     */
    public function notifyNearbyWorkers(Job $job, float $radiusKm = 50): array
    {
        if (! $job->lat || ! $job->lng) {
            return ['notified' => 0, 'radius_km' => $radiusKm, 'candidate_count' => 0, 'skipped' => 'no_coordinates'];
        }

        $lat = $job->lat;
        $lng = $job->lng;

        $workers = User::select([
            'users.*',
            DB::raw('
                (6371 * acos(
                    cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?))
                    + sin(radians(?)) * sin(radians(lat))
                )) AS distance_km
            '),
        ])
            ->setBindings([$lat, $lng, $lat])
            ->where('role', 'mfanyakazi')
            ->where('is_active', true)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->where('id', '!=', $job->user_id)
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km', 'asc')
            ->limit(50)
            ->get();

        $notified = 0;
        foreach ($workers as $worker) {
            $distance = (float) $worker->distance_km;
            if ($distance <= 5) {
                $label = 'Karibu Sana';
            } elseif ($distance <= 15) {
                $label = 'Karibu';
            } elseif ($distance <= 30) {
                $label = 'Wastani';
            } else {
                $label = 'Mbali';
            }

            try {
                $worker->notify(new JobAvailableNotification($job, $distance, $label));
                $notified++;
            } catch (\Throwable $e) {
                \Log::error("JobAvailable notify worker {$worker->id}: ".$e->getMessage());
            }
        }

        return [
            'notified' => $notified,
            'radius_km' => $radiusKm,
            'candidate_count' => $workers->count(),
        ];
    }
    
    /**
     * Check if a worker is within radius of a job
     */
    public function isWorkerNearby(User $worker, Job $job, float $radiusKm = 10): bool
    {
        if (!$worker->lat || !$worker->lng || !$job->lat || !$job->lng) {
            return false;
        }
        
        $distance = $this->calculateDistance(
            $worker->lat, 
            $worker->lng, 
            $job->lat, 
            $job->lng
        );
        
        return $distance <= $radiusKm;
    }
    
    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Send push notification to worker (placeholder for actual implementation)
     */
    private function sendPushNotification(User $worker, Job $job): void
    {
        // TODO: Implement with Firebase Cloud Messaging (FCM) or OneSignal
        // Example with FCM:
        /*
        $fcmToken = $worker->fcm_token; // Store in users table
        
        if ($fcmToken) {
            Http::withHeaders([
                'Authorization' => 'Bearer ' . env('FCM_SERVER_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $fcmToken,
                'notification' => [
                    'title' => 'Kazi Mpya Karibu Nawe!',
                    'body' => $job->title . ' - TZS ' . number_format($job->price),
                    'icon' => 'notification_icon',
                    'sound' => 'default',
                ],
                'data' => [
                    'job_id' => $job->id,
                    'type' => 'new_job',
                    'distance_km' => $this->calculateDistance(
                        $worker->lat, $worker->lng, 
                        $job->lat, $job->lng
                    ),
                ],
            ]);
        }
        */
    }
    
    /**
     * Send SMS notification (placeholder)
     */
    private function sendSMS(string $phone, string $message): void
    {
        // TODO: Integrate with SMS provider (e.g., Africa's Talking, Twilio)
        // Example:
        /*
        Http::post('https://api.africastalking.com/version1/messaging', [
            'username' => env('AT_USERNAME'),
            'to' => $phone,
            'message' => $message,
        ]);
        */
    }
}

