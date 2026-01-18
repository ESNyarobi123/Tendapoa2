<?php

namespace App\Services;

use App\Models\{Job, User};
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Send geo-based notifications to nearby workers
     * Uses Haversine formula to calculate distance
     * 
     * @param Job $job
     * @param float $radiusKm Default 10km radius
     * @return array Workers notified
     */
    public function notifyNearbyWorkers(Job $job, float $radiusKm = 10): array
    {
        $lat = $job->lat;
        $lng = $job->lng;
        $categoryId = $job->category_id;
        
        // Haversine formula to find workers within radius
        // Formula: distance = acos(sin(lat1)*sin(lat2) + cos(lat1)*cos(lat2)*cos(lng2-lng1)) * 6371
        $workers = User::select([
            'users.*',
            DB::raw("
                (6371 * acos(
                    cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) 
                    + sin(radians(?)) * sin(radians(lat))
                )) AS distance_km
            ")
        ])
        ->setBindings([$lat, $lng, $lat])
        ->where('role', 'mfanyakazi')
        ->where('is_active', true)
        ->whereNotNull('lat')
        ->whereNotNull('lng')
        ->having('distance_km', '<=', $radiusKm)
        ->orderBy('distance_km', 'asc')
        ->limit(50) // Max 50 nearby workers
        ->get();
        
        // Store notifications in database for workers to check
        $notificationData = [];
        foreach ($workers as $worker) {
            // Create notification record (can be checked via API)
            $notificationData[] = [
                'worker_id' => $worker->id,
                'job_id' => $job->id,
                'type' => 'new_job_nearby',
                'distance_km' => round($worker->distance_km, 2),
                'message' => "Kazi mpya karibu na wewe ({$worker->distance_km}km): {$job->title}",
                'read' => false,
                'created_at' => now(),
            ];
            
            // TODO: Send actual push notification (Firebase, OneSignal, etc)
            // $this->sendPushNotification($worker, $job);
            
            // TODO: Send SMS notification (optional)
            // $this->sendSMS($worker->phone, "Kazi mpya: {$job->title}");
        }
        
        // Store in session/cache or database table for API to retrieve
        // For now, workers will see jobs in feed API
        
        return [
            'workers_count' => $workers->count(),
            'radius_km' => $radiusKm,
            'workers' => $workers->pluck('id')->toArray(),
            'notifications_queued' => count($notificationData),
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

