<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    HomeController,
    DashboardController,
    JobController,
    PaymentController,
    FeedController,
    JobViewController,
    MyJobsController,
    WorkerActionsController,
    ChatController,
    WithdrawalController,
    AdminController
};
use App\Http\Controllers\Admin\WithdrawalAdminController;
use App\Http\Controllers\Api\DashboardController as ApiDashboardController;
use App\Models\Setting;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Base URL: /api
|
| All routes automatically return JSON responses.
|
*/

// ============================================================================
// 1. AUTHENTICATION APIs (Public)
// ============================================================================

Route::prefix('auth')->group(function () {
    // Register new user
    Route::post('/register', [AuthController::class, 'apiRegister']);
    
    
    // Login user
    Route::post('/login', [AuthController::class, 'apiLogin']);
    
    // Logout user (protected)
    Route::post('/logout', [AuthController::class, 'apiLogout'])->middleware('auth:sanctum');
    
    // Get current authenticated user
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    })->middleware('auth:sanctum');
    
    // Get user profile details
    Route::get('/getuser', [AuthController::class, 'getuser'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    // Njia ya kusajili FCM Token
    Route::post('/fcm-token', [AuthController::class, 'updateToken']);
});

// ============================================================================
// 2. PUBLIC APIs (No Authentication Required)
// ============================================================================

// Get categories list
Route::get('/categories', function () {
    $categories = \App\Models\Category::orderBy('name')->get();
    return response()->json([
        'success' => true,
        'data' => $categories
    ]);
});

// Get home page data
Route::get('/home', function () {
    $categories = \App\Models\Category::orderBy('name')->get();
    $stats = [
        'total_workers' => \App\Models\User::where('role', 'mfanyakazi')->count(),
        'completed_jobs' => \App\Models\Job::where('status', 'completed')->count(),
        'active_users' => \App\Models\User::count(),
    ];
    return response()->json([
        'success' => true,
        'data' => [
            'categories' => $categories,
            'stats' => $stats
        ]
    ]);
});

// Check nearby workers (for job posting)
Route::get('/workers/nearby', function (Request $request) {
    $request->validate([
        'lat' => ['required', 'numeric', 'between:-90,90'],
        'lng' => ['required', 'numeric', 'between:-180,180'],
        'radius' => ['nullable', 'numeric', 'min:1', 'max:50'], // km, default 5
    ]);
    
    $lat = (float) $request->lat;
    $lng = (float) $request->lng;
    $radiusKm = (float) ($request->radius ?? 5);
    
    // Haversine formula to calculate distance in km
    // Earth radius = 6371 km
    $workers = \App\Models\User::where('role', 'mfanyakazi')
        ->where('is_active', true)
        ->whereNotNull('lat')
        ->whereNotNull('lng')
        ->selectRaw("
            *,
            (6371 * acos(
                cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) +
                sin(radians(?)) * sin(radians(lat))
            )) AS distance_km
        ", [$lat, $lng, $lat])
        ->having('distance_km', '<=', $radiusKm)
        ->orderBy('distance_km')
        ->get();
    
    $workerCount = $workers->count();
    
    // Determine message based on worker count
    if ($workerCount === 0) {
        $message = "Pole, hakuna wafanyakazi eneo hili kwa sasa (ndani ya km {$radiusKm}). Unaweza kuendelea lakini muda wa kupata mfanyakazi unaweza kuwa mrefu.";
        $status = 'no_workers';
    } elseif ($workerCount === 1) {
        $message = "Kuna mfanyakazi 1 karibu nawe ndani ya km {$radiusKm}!";
        $status = 'workers_found';
    } else {
        $message = "Kuna wafanyakazi {$workerCount} karibu nawe ndani ya km {$radiusKm}!";
        $status = 'workers_found';
    }
    
    return response()->json([
        'success' => true,
        'data' => [
            'worker_count' => $workerCount,
            'radius_km' => $radiusKm,
            'status' => $status,
            'message' => $message,
            // Don't expose worker details for privacy, just counts by distance
            'by_distance' => [
                'within_1km' => $workers->where('distance_km', '<=', 1)->count(),
                'within_3km' => $workers->where('distance_km', '<=', 3)->count(),
                'within_5km' => $workers->where('distance_km', '<=', 5)->count(),
            ]
        ]
    ]);
});

// Get system settings (Public)
Route::get('/settings', function () {
    $settings = Setting::pluck('value', 'key')->toArray();
    $publicKeys = ['platform_name', 'platform_version', 'system_currency', 'commission_rate', 'min_withdrawal', 'withdrawal_fee', 'job_posting_fee', 'payments_enabled', 'platform_logo'];
    $filtered = [];
    foreach ($publicKeys as $k) $filtered[$k] = $settings[$k] ?? null;
    return response()->json(['success' => true, 'data' => $filtered]);
});

// ============================================================================
// 3. PROTECTED APIs (Authentication Required)
// ============================================================================

// Using 'auth' for session-based authentication (works with web login)
// To use API tokens, install Laravel Sanctum and change to 'auth:sanctum'
Route::middleware(['force.json', 'auth:sanctum'])->group(function () {
    
    // ========================================================================
    // DASHBOARD APIs
    // ========================================================================
    
    Route::prefix('dashboard')->group(function () {
        // Get dashboard data (role-based)
        Route::match(['get', 'post'], '/', function (Request $request) {
            $user = $request->user();
            
            if ($user->role === 'admin') {
                $data = [
                    'role' => 'admin',
                    'jobsCount' => \App\Models\Job::count(),
                    'paidTotal' => \App\Models\Payment::where('status', 'COMPLETED')->sum('amount'),
                    'usersWorkers' => \App\Models\User::where('role', 'mfanyakazi')->count(),
                    'usersClients' => \App\Models\User::where('role', 'muhitaji')->count(),
                ];
            } elseif ($user->role === 'muhitaji') {
                $data = [
                    'role' => 'muhitaji',
                    'posted' => \App\Models\Job::where('user_id', $user->id)->count(),
                    'completed' => \App\Models\Job::where('user_id', $user->id)->where('status', 'completed')->count(),
                    'totalPaid' => \App\Models\Payment::whereHas('job', fn($q) => $q->where('user_id', $user->id))
                        ->where('status', 'COMPLETED')
                        ->sum('amount'),
                    'paymentHistory' => \App\Models\Payment::whereHas('job', fn($q) => $q->where('user_id', $user->id))
                        ->with('job')
                        ->latest()
                        ->limit(10)
                        ->get(),
                    'allJobs' => \App\Models\Job::where('user_id', $user->id)
                        ->with('acceptedWorker', 'category', 'payment')
                        ->latest()
                        ->limit(10)
                        ->get(),
                ];
            } else {
                // Mfanyakazi
                $wallet = $user->ensureWallet();
                $data = [
                    'role' => 'mfanyakazi',
                    'done' => \App\Models\Job::where('accepted_worker_id', $user->id)->where('status', 'completed')->count(),
                    'earnTotal' => \App\Models\WalletTransaction::where('user_id', $user->id)
                        ->where('type', 'EARN')
                        ->where('amount', '>', 0)
                        ->sum('amount'),
                    'withdrawn' => \App\Models\Withdrawal::where('user_id', $user->id)->whereIn('status', ['PAID', 'PROCESSING'])->sum('amount'),
                    'available' => $wallet->balance,
                    'currentJobs' => \App\Models\Job::with('muhitaji', 'category')
                        ->where('accepted_worker_id', $user->id)
                        ->whereIn('status', ['assigned', 'in_progress', 'ready_for_confirmation'])
                        ->latest()
                        ->limit(5)
                        ->get(),
                    'thisMonthEarnings' => \App\Models\Job::where('accepted_worker_id', $user->id)
                        ->where('status', 'completed')
                        ->where('completed_at', '>=', now()->startOfMonth())
                        ->sum('price'),
                    'earningsHistory' => \App\Models\WalletTransaction::where('user_id', $user->id)
                        ->where('type', 'EARN')
                        ->where('amount', '>', 0)
                        ->latest()
                        ->limit(10)
                        ->get(),
                    'withdrawalsHistory' => \App\Models\Withdrawal::where('user_id', $user->id)
                        ->latest()
                        ->limit(10)
                        ->get(),
                    'completedJobs' => \App\Models\Job::where('accepted_worker_id', $user->id)
                        ->where('status', 'completed')
                        ->with('muhitaji', 'category')
                        ->latest()
                        ->limit(10)
                        ->get(),
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        });
        
        // Get real-time updates
        Route::get('/updates', [ApiDashboardController::class, 'updates']);
    });
    
    // ========================================================================
    // JOB MANAGEMENT APIs - MUHITAJI
    // ========================================================================
    
    Route::prefix('jobs')->group(function () {
        // Create job (Muhitaji)
        Route::post('/', function (Request $request) {
            $user = $request->user();
            if (!in_array($user->role, ['muhitaji', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Huna ruhusa (muhitaji/admin tu).'
                ], 403);
            }
            
            $validated = $request->validate([
                'title' => ['required', 'max:120'],
                'category_id' => ['required', 'exists:categories,id'],
                'price' => ['required', 'integer', 'min:500'],
                'lat' => ['required', 'numeric', 'between:-90,90'],
                'lng' => ['required', 'numeric', 'between:-180,180'],
                'phone' => ['required', 'regex:/^(0[6-7]\d{8}|255[6-7]\d{8})$/'],
                'description' => ['nullable'],
                'address_text' => ['nullable'],
            ]);
            
            $paymentsEnabled = Setting::get('payments_enabled', '1') == '1';
            $job = \App\Models\Job::create([
                'user_id' => $user->id,
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'address_text' => $validated['address_text'] ?? null,
                'status' => $paymentsEnabled ? 'pending_payment' : 'posted',
                'published_at' => $paymentsEnabled ? null : now(),
            ]);

            if (!$paymentsEnabled) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'job' => $job->load('category'),
                        'payment_required' => false
                    ],
                    'message' => 'Kazi imechapishwa kwa mafanikio!'
                ], 201);
            }
            
            // Create payment record
            $orderId = (string) \Illuminate\Support\Str::ulid();
            $payment = $job->payment()->create([
                'order_id' => $orderId,
                'amount' => $job->price,
                'status' => 'PENDING',
            ]);
            
            // Initialize ZenoPay payment
            $zenoService = app(\App\Services\ZenoPayService::class);
            $payload = [
                'order_id' => $orderId,
                'buyer_email' => $user->email ?? 'client@tendapoa.local',
                'buyer_name' => $user->name ?? 'Client',
                'buyer_phone' => $validated['phone'],
                'amount' => $job->price,
                'amount' => $job->price,
                // 'webhook_url' => route('zeno.webhook'), // User requested polling only
            ];
            
            $res = $zenoService->startPayment($payload);
            
            return response()->json([
                'success' => $res['ok'],
                'data' => [
                    'job' => $job->load('category', 'payment'),
                    'payment' => $payment,
                    'zenopay_response' => $res
                ],
                'message' => $res['ok'] ? 'Kazi imeundwa. Fanya malipo.' : 'Imeshindikana kuanzisha malipo.'
            ], $res['ok'] ? 201 : 400);
        });
        
        // Get my jobs (Muhitaji) - using controller method
        Route::get('/my', [MyJobsController::class, 'apiIndex']);
        
        // Get job details - using controller method
        Route::get('/{job}', [JobViewController::class, 'apiShow']);
        
        // Post comment/application on job
        Route::post('/{job}/comment', function (Request $request, \App\Models\Job $job) {
            $user = $request->user();
            if (!in_array($user->role, ['mfanyakazi', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Huna ruhusa (mfanyakazi/admin tu).'
                ], 403);
            }
            
            $validated = $request->validate([
                'message' => ['required', 'max:1000'],
                'bid_amount' => ['nullable', 'integer', 'min:0'],
                'is_application' => ['nullable', 'boolean'],
            ]);
            
            $comment = \App\Models\JobComment::create([
                'work_order_id' => $job->id,
                'user_id' => $user->id,
                'message' => $validated['message'],
                'is_application' => $request->boolean('is_application'),
                'bid_amount' => $validated['bid_amount'] ?? null,
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $comment->load('user'),
                'message' => 'Maoni yamewekwa.'
            ], 201);
        });
        
        // Accept worker application (Muhitaji)
        Route::post('/{job}/accept/{comment}', function (\App\Models\Job $job, \App\Models\JobComment $comment, Request $request) {
            $user = $request->user();
            if (!in_array($user->role, ['muhitaji', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Huna ruhusa (muhitaji/admin tu).'
                ], 403);
            }
            
            if ($job->user_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hii sio kazi yako.'
                ], 403);
            }
            
            $job->update([
                'accepted_worker_id' => $comment->user_id,
                'status' => 'assigned',
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $job->load('acceptedWorker'),
                'message' => 'Umemchagua mfanyakazi.'
            ]);
        });
        
        // Get job edit data
        Route::get('/{job}/edit', [JobController::class, 'apiEdit']);
        
        // Update job (edit)
        Route::put('/{job}', [JobController::class, 'apiUpdate']);
        
        // Poll payment status
        Route::get('/{job}/poll', function (\App\Models\Job $job) {
            $payment = $job->payment;
            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }
            
            if ($payment->status === 'COMPLETED') {
                return response()->json([
                    'success' => true,
                    'done' => true,
                    'status' => 'COMPLETED'
                ]);
            }
            
            // Check ZenoPay
            $zenoService = app(\App\Services\ZenoPayService::class);
            $resp = $zenoService->checkOrder($payment->order_id);
            
            if ($resp['ok'] && ($resp['json']['payment_status'] ?? null) === 'COMPLETED') {
                $payment->update([
                    'status' => 'COMPLETED',
                    'resultcode' => $resp['json']['resultcode'] ?? null,
                    'reference' => $resp['json']['reference'] ?? null,
                    'meta' => $resp['json'],
                ]);
            }
            
            // Activate the job if payment completed
            if ($payment->status === 'COMPLETED' && $job->status === 'pending_payment') {
                $job->update([
                    'status' => 'posted',
                    'published_at' => now(),
                ]);
            }
            
            return response()->json([
                'success' => true,
                'done' => $payment->status === 'COMPLETED',
                'status' => $payment->status
            ]);
        });
        
        // Check payment status (API method)
        Route::get('/{job}/payment-status', [PaymentController::class, 'apiPoll']);
    });
    
    // ========================================================================
    // JOB MANAGEMENT APIs - MFANYAKAZI (Worker)
    // ========================================================================
    
    Route::prefix('worker')->group(function () {
        // Create job as Mfanyakazi
        Route::post('/jobs', function (Request $request) {
            $user = $request->user();
            if ($user->role !== 'mfanyakazi') {
                return response()->json([
                    'success' => false,
                    'message' => 'Huna ruhusa. Mfanyakazi tu.'
                ], 403);
            }
            
            $validated = $request->validate([
                'title' => ['required', 'max:120'],
                'category_id' => ['required', 'exists:categories,id'],
                'description' => ['required', 'min:20'],
                'price' => ['required', 'integer', 'min:1000'],
                'lat' => ['required', 'numeric', 'between:-90,90'],
                'lng' => ['required', 'numeric', 'between:-180,180'],
                'phone' => ['required', 'regex:/^(0[6-7]\d{8}|255[6-7]\d{8})$/'],
                'address_text' => ['nullable'],
            ]);
            
            $postingFee = (int) Setting::get('job_posting_fee', 0);
            $paymentsEnabled = Setting::get('payments_enabled', '1') == '1';
            $wallet = $user->ensureWallet();
            
            if ($postingFee <= 0) {
                $job = \App\Models\Job::create([
                    'user_id' => $user->id,
                    'category_id' => $validated['category_id'],
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'price' => $validated['price'],
                    'lat' => $validated['lat'],
                    'lng' => $validated['lng'],
                    'address_text' => $validated['address_text'] ?? null,
                    'status' => 'posted',
                    'published_at' => now(),
                    'poster_type' => 'mfanyakazi',
                    'posting_fee' => 0,
                ]);
                return response()->json(['success' => true, 'message' => 'Kazi imechapishwa kwa mafanikio!', 'payment_method' => 'free'], 201);
            }
            
            if ($wallet->balance >= $postingFee) {
                // Deduct from wallet
                \Illuminate\Support\Facades\DB::transaction(function () use ($user, $validated, $postingFee, $wallet) {
                    $job = \App\Models\Job::create([
                        'user_id' => $user->id,
                        'category_id' => $validated['category_id'],
                        'title' => $validated['title'],
                        'description' => $validated['description'],
                        'price' => $validated['price'],
                        'lat' => $validated['lat'],
                        'lng' => $validated['lng'],
                        'address_text' => $validated['address_text'] ?? null,
                        'status' => 'posted',
                        'published_at' => now(),
                        'poster_type' => 'mfanyakazi',
                        'posting_fee' => $postingFee,
                    ]);
                    
                    $wallet->decrement('balance', $postingFee);
                    
                    \App\Models\WalletTransaction::create([
                        'user_id' => $user->id,
                        'type' => 'debit',
                        'amount' => $postingFee,
                        'description' => "Job posting fee for: {$job->title}",
                        'reference' => "JOB_POST_{$job->id}",
                    ]);
                });
                
                return response()->json([
                    'success' => true,
                    'message' => 'Kazi imechapishwa kwa mafanikio!',
                    'payment_method' => 'wallet'
                ], 201);
            } else {
                // Need ZenoPay payment
                $job = \App\Models\Job::create([
                    'user_id' => $user->id,
                    'category_id' => $validated['category_id'],
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'price' => $validated['price'],
                    'lat' => $validated['lat'],
                    'lng' => $validated['lng'],
                    'address_text' => $validated['address_text'] ?? null,
                    'status' => 'pending_payment',
                    'poster_type' => 'mfanyakazi',
                    'posting_fee' => $postingFee,
                ]);
                
                $orderId = (string) \Illuminate\Support\Str::ulid();
                $payment = $job->payment()->create([
                    'order_id' => $orderId,
                    'amount' => $postingFee,
                    'status' => 'PENDING',
                ]);
                
                $zenoService = app(\App\Services\ZenoPayService::class);
                $payload = [
                    'order_id' => $orderId,
                    'buyer_email' => $user->email ?? 'worker@tendapoa.local',
                    'buyer_name' => $user->name ?? 'Worker',
                    'buyer_phone' => $validated['phone'],
                    'amount' => $postingFee,

                    // 'webhook_url' => route('zeno.webhook'), // User requested polling only
                ];
                
                $res = $zenoService->startPayment($payload);
                
                return response()->json([
                    'success' => $res['ok'],
                    'data' => [
                        'job' => $job->load('category', 'payment'),
                        'payment' => $payment,
                        'zenopay_response' => $res
                    ],
                    'message' => $res['ok'] ? 'Fanya malipo ya ada ya kuchapisha.' : 'Imeshindikana kuanzisha malipo.',
                    'payment_method' => 'zenopay'
                ], $res['ok'] ? 201 : 400);
            }
        });
        
        // Get assigned jobs - using controller method
        Route::get('/assigned', [WorkerActionsController::class, 'apiAssigned']);
        
        // Accept assigned job - using controller method
        Route::post('/jobs/{job}/accept', [WorkerActionsController::class, 'apiAccept']);
        
        // Decline assigned job - using controller method
        Route::post('/jobs/{job}/decline', [WorkerActionsController::class, 'apiDecline']);
        
        // Complete job with code - using controller method
        Route::post('/jobs/{job}/complete', [WorkerActionsController::class, 'apiComplete']);
    });
    
    // ========================================================================
    // FEED APIs (Job Browse)
    // ========================================================================
    
    Route::prefix('feed')->group(function () {
        // Get jobs feed with distance calculation
        Route::match(['get', 'post'], '/', [FeedController::class, 'apiIndex']);
        
        // Get jobs for map view
        Route::match(['get', 'post'], '/map', [FeedController::class, 'apiMap']);
    });
    
    // ========================================================================
    // CHAT APIs
    // ========================================================================
    
    Route::prefix('chat')->group(function () {
        // Get all conversations
        Route::get('/', function (Request $request) {
            $user = $request->user();
            
            $conversations = \Illuminate\Support\Facades\DB::table('private_messages')
                ->select(
                    'work_order_id',
                    \Illuminate\Support\Facades\DB::raw('MAX(created_at) as last_message_at'),
                    \Illuminate\Support\Facades\DB::raw("COUNT(CASE WHEN receiver_id = {$user->id} AND is_read = 0 THEN 1 END) as unread_count")
                )
                ->where(function($query) use ($user) {
                    $query->where('sender_id', $user->id)
                          ->orWhere('receiver_id', $user->id);
                })
                ->groupBy('work_order_id')
                ->orderBy('last_message_at', 'desc')
                ->get();
            
            $jobIds = $conversations->pluck('work_order_id');
            $jobs = \App\Models\Job::with(['muhitaji', 'acceptedWorker', 'category'])
                ->whereIn('id', $jobIds)
                ->get()
                ->keyBy('id');
            
            $conversations = $conversations->map(function($conv) use ($jobs, $user) {
                $job = $jobs->get($conv->work_order_id);
                if (!$job) return null;
                
                $otherUser = $user->id === $job->user_id 
                    ? $job->acceptedWorker 
                    : $job->muhitaji;
                
                return [
                    'job' => $job,
                    'other_user' => $otherUser,
                    'last_message_at' => $conv->last_message_at,
                    'unread_count' => $conv->unread_count,
                ];
            })->filter()->values();
            
            return response()->json([
                'success' => true,
                'data' => $conversations
            ]);
        });
        
        // Get messages for specific job
        Route::get('/{job}', function (\App\Models\Job $job, Request $request) {
            $user = $request->user();
            
            $isMuhitaji = $job->user_id === $user->id;
            $hasCommented = $job->comments()->where('user_id', $user->id)->exists();
            $isAcceptedWorker = $job->accepted_worker_id === $user->id;
            
            if (!$isMuhitaji && !$hasCommented && !$isAcceptedWorker) {
                return response()->json([
                    'success' => false,
                    'message' => 'Huna ruhusa ya kuona mazungumzo haya. Tuma comment kwanza.'
                ], 403);
            }
            
            $workerId = $request->get('worker_id');
            if ($isMuhitaji && $workerId) {
                $otherUser = \App\Models\User::find($workerId);
            } elseif ($isMuhitaji) {
                $otherUser = $job->acceptedWorker;
            } else {
                $otherUser = $job->muhitaji;
            }
            
            if (!$otherUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mtumiaji mwingine hajapatikana.'
                ], 404);
            }
            
            $messages = \App\Models\PrivateMessage::forJob($job->id)
                ->betweenUsers($user->id, $otherUser->id)
                ->with(['sender', 'receiver'])
                ->orderBy('created_at', 'asc')
                ->get();
            
            // Mark as read
            \App\Models\PrivateMessage::forJob($job->id)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
            
            $unreadCount = \App\Models\PrivateMessage::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'job' => $job->load('muhitaji', 'acceptedWorker', 'category'),
                    'other_user' => $otherUser,
                    'messages' => $messages,
                    'unread_count' => $unreadCount
                ]
            ]);
        });
        
        // Send message
        Route::post('/{job}/send', function (\App\Models\Job $job, Request $request) {
            $user = $request->user();
            
            $isMuhitaji = $job->user_id === $user->id;
            $hasCommented = $job->comments()->where('user_id', $user->id)->exists();
            $isAcceptedWorker = $job->accepted_worker_id === $user->id;
            
            if (!$isMuhitaji && !$hasCommented && !$isAcceptedWorker) {
                return response()->json([
                    'success' => false,
                    'message' => 'Huna ruhusa ya kutuma ujumbe. Tuma comment kwanza.'
                ], 403);
            }
            
            $validated = $request->validate([
                'message' => 'required|string|max:5000',
                'receiver_id' => 'nullable|exists:users,id',
            ]);
            
            if ($isMuhitaji) {
                $receiverId = $validated['receiver_id'] ?? $job->accepted_worker_id;
            } else {
                $receiverId = $job->user_id;
            }
            
            if (!$receiverId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mpokeaji hajapatikana.'
                ], 400);
            }
            
            $message = \App\Models\PrivateMessage::create([
                'work_order_id' => $job->id,
                'sender_id' => $user->id,
                'receiver_id' => $receiverId,
                'message' => $validated['message'],
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $message->load('sender', 'receiver'),
                'message' => 'Ujumbe umetumwa.'
            ], 201);
        });
        
        // Poll for new messages
        Route::get('/{job}/poll', function (\App\Models\Job $job, Request $request) {
            $user = $request->user();
            
            $isMuhitaji = $job->user_id === $user->id;
            $hasCommented = $job->comments()->where('user_id', $user->id)->exists();
            $isAcceptedWorker = $job->accepted_worker_id === $user->id;
            
            if (!$isMuhitaji && !$hasCommented && !$isAcceptedWorker) {
                return response()->json([
                    'success' => false,
                    'message' => 'Huna ruhusa.'
                ], 403);
            }
            
            $lastId = $request->get('last_id', 0);
            $otherUserId = $request->get('other_user_id');
            
            if (!$otherUserId) {
                $otherUserId = $user->id === $job->user_id 
                    ? $job->accepted_worker_id 
                    : $job->user_id;
            }
            
            $newMessages = \App\Models\PrivateMessage::forJob($job->id)
                ->betweenUsers($user->id, $otherUserId)
                ->where('id', '>', $lastId)
                ->with('sender')
                ->orderBy('created_at', 'asc')
                ->get();
            
            // Mark as read
            \App\Models\PrivateMessage::forJob($job->id)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'messages' => $newMessages,
                    'count' => $newMessages->count()
                ]
            ]);
        });
        
        // Get unread count
        Route::get('/unread-count', function (Request $request) {
            $count = \App\Models\PrivateMessage::where('receiver_id', $request->user()->id)
                ->where('is_read', false)
                ->count();
            
            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        })->name('api.chat.unread');
    });
    
    // ========================================================================
    // WITHDRAWAL APIs
    // ========================================================================
    
    Route::prefix('withdrawal')->group(function () {
        // Get wallet balance
        Route::get('/wallet', function (Request $request) {
            $user = $request->user();
            if (!in_array($user->role, ['mfanyakazi', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Huna ruhusa (mfanyakazi/admin tu).'
                ], 403);
            }
            
            $wallet = $user->ensureWallet();
            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => $wallet->balance,
                    'user_id' => $user->id,
                    'created_at' => $wallet->created_at
                ]
            ]);
        });
        
        // Submit withdrawal request
        Route::post('/submit', function (Request $request) {
            $user = $request->user();
            if (!in_array($user->role, ['mfanyakazi', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Huna ruhusa (mfanyakazi/admin tu).'
                ], 403);
            }
            
            $validated = $request->validate([
                'amount' => ['required', 'integer', 'min:' . (Setting::get('min_withdrawal', 5000))],
                'phone_number' => ['required', 'string', 'min:10'],
                'registered_name' => ['required', 'string', 'min:2'],
                'network_type' => ['required', 'string', 'in:vodacom,tigo,airtel,halotel,ttcl'],
                'method' => ['required', 'string'],
            ]);
            
            $wallet = $user->ensureWallet();
            $withdrawalFee = (int) Setting::get('withdrawal_fee', 0);
            $totalToDebit = $validated['amount'] + $withdrawalFee;
            if ($wallet->balance < $totalToDebit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salio lako halitoshi kulipia kiasi unachotoa pamoja na makato ya TZS ' . number_format($withdrawalFee)
                ], 422);
            }
            
            // Debit wallet
            $walletService = app(\App\Services\WalletService::class);
            $walletService->debit($user, $totalToDebit, 'WITHDRAW', 'Withdrawal request (inc. fee: TZS ' . $withdrawalFee . ')');
            
            $withdrawal = \App\Models\Withdrawal::create([
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'status' => 'PROCESSING',
                'method' => $validated['method'],
                'account' => $validated['phone_number'],
                'registered_name' => $validated['registered_name'],
                'network_type' => $validated['network_type'],
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $withdrawal,
                'message' => 'Withdrawal imewasilishwa. Subiri uthibitisho wa Admin.'
            ], 201);
        });
        
        // Get withdrawal history
        Route::get('/history', function (Request $request) {
            $user = $request->user();
            if (!in_array($user->role, ['mfanyakazi', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Huna ruhusa (mfanyakazi/admin tu).'
                ], 403);
            }
            
            $withdrawals = \App\Models\Withdrawal::where('user_id', $user->id)
                ->latest()
                ->paginate(20);
            
            return response()->json([
                'success' => true,
                'data' => $withdrawals
            ]);
        });
    });
    
    // ========================================================================
    // ADMIN APIs
    // ========================================================================
    
    Route::prefix('admin')->middleware('admin')->group(function () {
        // Get all users
        Route::get('/users', function (Request $request) {
            $search = $request->get('search');
            $role = $request->get('role');
            
            $query = \App\Models\User::with('wallet');
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }
            
            if ($role) {
                $query->where('role', $role);
            }
            
            $users = $query->latest()->paginate(20);
            
            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        });
        
        // Get user details
        Route::get('/users/{user}', function (\App\Models\User $user) {
            $user->load('wallet', 'jobs', 'assignedJobs', 'withdrawals');
            
            $stats = [
                'jobs_posted' => $user->jobs()->count(),
                'jobs_assigned' => $user->assignedJobs()->count(),
                'jobs_completed' => $user->assignedJobs()->where('status', 'completed')->count(),
                'wallet_balance' => $user->wallet->balance ?? 0,
                'total_earned' => \App\Models\WalletTransaction::where('user_id', $user->id)
                    ->where('type', 'credit')
                    ->sum('amount'),
                'total_withdrawn' => \App\Models\Withdrawal::where('user_id', $user->id)
                    ->where('status', 'paid')
                    ->sum('amount'),
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'stats' => $stats
                ]
            ]);
        });
        
        // Get all jobs
        Route::get('/jobs', function (Request $request) {
            $status = $request->get('status');
            $search = $request->get('search');
            
            $query = \App\Models\Job::with(['muhitaji', 'acceptedWorker', 'category']);
            
            if ($status) {
                $query->where('status', $status);
            }
            
            if ($search) {
                $query->where('title', 'like', "%{$search}%");
            }
            
            $jobs = $query->latest()->paginate(20);
            
            return response()->json([
                'success' => true,
                'data' => $jobs
            ]);
        });
        
        // Get all withdrawals
        Route::get('/withdrawals', function () {
            $withdrawals = \App\Models\Withdrawal::with('user')
                ->latest()
                ->paginate(20);
            
            return response()->json([
                'success' => true,
                'data' => $withdrawals
            ]);
        });
        
        // Mark withdrawal as paid
        Route::post('/withdrawals/{withdrawal}/paid', function (\App\Models\Withdrawal $withdrawal) {
            $withdrawal->update(['status' => 'PAID']);
            
            return response()->json([
                'success' => true,
                'message' => 'Withdrawal marked as PAID.'
            ]);
        });
        
        // Reject withdrawal
        Route::post('/withdrawals/{withdrawal}/reject', function (\App\Models\Withdrawal $withdrawal) {
            if ($withdrawal->status === 'PAID') {
                return response()->json([
                    'success' => false,
                    'message' => 'Already paid; cannot reject.'
                ], 422);
            }
            
            // Refund to wallet
            $walletService = app(\App\Services\WalletService::class);
            $walletService->credit($withdrawal->user, $withdrawal->amount, 'ADJUST', 'Withdrawal rejected refund');
            
            $withdrawal->update(['status' => 'REJECTED']);
            
            return response()->json([
                'success' => true,
                'message' => 'Withdrawal rejected & refunded.'
            ]);
        });
        
        // Force complete job
        Route::post('/jobs/{job}/force-complete', function (\App\Models\Job $job) {
            $job->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Job '{$job->title}' has been force completed!"
            ]);
        });
        
        // Force cancel job
        Route::post('/jobs/{job}/force-cancel', function (\App\Models\Job $job) {
            $job->update(['status' => 'cancelled']);
            
            return response()->json([
                'success' => true,
                'message' => "Job '{$job->title}' has been force cancelled!"
            ]);
        });
        
        // Get analytics
        Route::get('/analytics', function (Request $request) {
            $period = $request->get('period', 30);
            
            $userGrowth = \App\Models\User::select(
                    \Illuminate\Support\Facades\DB::raw('DATE(created_at) as date'),
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subDays($period))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            $jobStats = \App\Models\Job::select(
                    \Illuminate\Support\Facades\DB::raw('DATE(created_at) as date'),
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subDays($period))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            $revenue = \App\Models\Payment::select(
                    \Illuminate\Support\Facades\DB::raw('DATE(created_at) as date'),
                    \Illuminate\Support\Facades\DB::raw('SUM(amount) as total')
                )
                ->where('status', 'COMPLETED')
                ->where('created_at', '>=', now()->subDays($period))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'period' => $period,
                    'user_growth' => $userGrowth,
                    'job_stats' => $jobStats,
                    'revenue' => $revenue
                ]
            ]);
        });
    });
});

// ============================================================================
// PUBLIC HEALTH CHECK (No Authentication)
// ============================================================================

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0',
        'message' => 'Tendapoa API is running'
    ]);
});

// ============================================================================
// WEBHOOK (No Authentication)
// ============================================================================

// ZenoPay webhook
Route::post('/payment/zeno/webhook', [PaymentController::class, 'webhook']);