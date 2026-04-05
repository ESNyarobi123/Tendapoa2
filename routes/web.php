<?php

use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\WithdrawalAdminController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\OtpPasswordResetController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CompletionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FundingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobViewController;
use App\Http\Controllers\MyJobsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\WorkerActionsController;
use App\Http\Controllers\WorkerApplicationsController;
use App\Models\Dispute;
use App\Models\Job;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

// Serve storage files - this route handles all storage requests
// Must be before other routes to catch storage requests first
// This is critical for php artisan serve which may not handle symlinks properly
// Serve storage files via PHP to bypass symlink issues
// Using /image/ prefix ensures request hits Laravel router instead of failing at web server level
Route::match(['get', 'head', 'options'], '/image/{path}', function ($path) {
    // Handle CORS preflight request
    if (request()->isMethod('OPTIONS')) {
        return response('', 200, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' => '*',
            'Access-Control-Max-Age' => '86400',
        ]);
    }

    try {
        // Decode the path in case it's URL encoded
        $path = urldecode($path);

        // Remove any leading/trailing slashes
        $path = trim($path, '/');

        // Build the full file path
        $filePath = storage_path('app/public/'.$path);

        // Security: prevent directory traversal
        $normalizedPath = str_replace('\\', '/', realpath($filePath) ?: $filePath);
        $normalizedStorage = str_replace('\\', '/', realpath(storage_path('app/public')) ?: storage_path('app/public'));

        // Check if path is within storage directory
        if (strpos($normalizedPath, $normalizedStorage) !== 0) {
            Log::warning('Storage access attempt outside public directory', [
                'path' => $path,
                'filePath' => $filePath,
                'normalizedPath' => $normalizedPath,
                'normalizedStorage' => $normalizedStorage,
            ]);
            abort(403, 'Access denied');
        }

        // Check if file exists
        if (! file_exists($filePath)) {
            // Log detailed information for debugging
            $storageBase = storage_path('app/public');
            $jobsDir = storage_path('app/public/jobs');
            $jobsFiles = [];
            if (is_dir($jobsDir)) {
                $allFiles = scandir($jobsDir);
                $jobsFiles = array_slice(array_filter($allFiles, function ($f) {
                    return $f !== '.' && $f !== '..';
                }), 0, 10);
            }

            Log::warning('Storage file not found - Route handler', [
                'requestedPath' => $path,
                'fullFilePath' => $filePath,
                'storageBase' => $storageBase,
                'storageBaseExists' => is_dir($storageBase),
                'storageBaseWritable' => is_dir($storageBase) ? is_writable($storageBase) : false,
                'jobsDir' => $jobsDir,
                'jobsDirExists' => is_dir($jobsDir),
                'jobsDirWritable' => is_dir($jobsDir) ? is_writable($jobsDir) : false,
                'jobsDirFilesCount' => is_dir($jobsDir) ? count(glob($jobsDir.'/*')) : 0,
                'sampleJobsFiles' => $jobsFiles,
                'symlinkExists' => is_link(public_path('storage')),
                'symlinkTarget' => is_link(public_path('storage')) ? readlink(public_path('storage')) : null,
            ]);
            abort(404, 'File not found: '.basename($path));
        }

        // Check if it's a file (not directory)
        if (! is_file($filePath)) {
            abort(404, 'Not a file');
        }

        // Check if readable
        if (! is_readable($filePath)) {
            Log::warning('Storage file not readable', [
                'path' => $path,
                'permissions' => substr(sprintf('%o', fileperms($filePath)), -4),
            ]);
            abort(403, 'File not accessible');
        }

        // Determine MIME type
        $mimeType = mime_content_type($filePath);
        if (! $mimeType) {
            // Fallback based on extension
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
            ];
            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        }

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' => '*',
        ]);
    } catch (HttpException $e) {
        // Re-throw HTTP exceptions (404, 403, etc.)
        throw $e;
    } catch (Exception $e) {
        Log::error('Storage file serving error', [
            'path' => $path ?? 'unknown',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
        abort(500, 'Error serving file: '.$e->getMessage());
    }
})->where('path', '.*')->name('storage.serve');

// Storage route with CORS for Flutter web
Route::match(['get', 'head', 'options'], '/storage/{path}', function ($path) {
    // Handle CORS preflight request
    if (request()->isMethod('OPTIONS')) {
        return response('', 200, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' => '*',
            'Access-Control-Max-Age' => '86400',
        ]);
    }

    $filePath = storage_path('app/public/'.$path);

    if (! file_exists($filePath) || ! is_file($filePath)) {
        abort(404);
    }

    $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
        'Access-Control-Allow-Headers' => '*',
    ]);
})->where('path', '.*')->name('storage.cors');

// HOME / LANDING
Route::get('/', [HomeController::class, 'index'])->name('home');

// Fees & Payments Policy
Route::get('/fees-payments-policy', function () {
    return view('policy.fees-payments');
})->name('policy.fees-payments');

// Terms and Conditions Policy
Route::get('/terms-and-conditions', function () {
    return view('policy.terms');
})->name('policy.terms');

// Privacy Policy
Route::get('/privacy-policy', function () {
    return view('policy.privacy');
})->name('policy.privacy');

// APK Download (Public)
Route::get('/download/app', [HomeController::class, 'downloadApp'])->name('app.download');

// Auth routes are loaded from auth.php

// AUTH-protected (🚫 hakuna 'role' middleware tena)
Route::middleware(['auth'])->group(function () {
    // PROFILE
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // MUHITAJI: create + pay (role check tutafanya ndani ya controller)
    Route::get('/jobs/create', [JobController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/{job}/wait', [JobController::class, 'wait'])->name('jobs.pay.wait');
    Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])->name('jobs.edit');
    Route::put('/jobs/{job}', [JobController::class, 'update'])->name('jobs.update');
    Route::post('/jobs/{job}/retry-payment', [JobController::class, 'retryPayment'])->name('jobs.pay.retry');

    // MFANYAKAZI: create + pay jobs (role check tutafanya ndani ya controller)
    Route::get('/jobs/create-mfanyakazi', [JobController::class, 'createMfanyakazi'])->name('jobs.create-mfanyakazi');
    Route::post('/jobs-mfanyakazi', [JobController::class, 'storeMfanyakazi'])->name('jobs.store-mfanyakazi');

    // Poll status (generic)
    Route::get('/jobs/{job}/poll', [PaymentController::class, 'poll'])->name('jobs.pay.poll');

    // MFANYAKAZI feed + view + comment (role check ndani ya controller)
    Route::get('/feed', [FeedController::class, 'index'])->name('feed');
    Route::get('/jobs/{job}', [JobViewController::class, 'show'])->name('jobs.show');
    Route::post('/jobs/{job}/comment', [CommentController::class, 'store'])->name('jobs.comment');

    // MUHITAJI accept/reject/counter (role check ndani ya controller)
    Route::post('/jobs/{job}/accept/{comment}', [CommentController::class, 'accept'])->name('jobs.accept');
    Route::post('/jobs/{job}/reject/{comment}', [CommentController::class, 'reject'])->name('jobs.reject');
    Route::post('/jobs/{job}/reply/{comment}', [CommentController::class, 'reply'])->name('jobs.reply');
    Route::post('/jobs/{job}/counter/{comment}', [CommentController::class, 'counterOffer'])->name('jobs.counter');
    Route::post('/jobs/{job}/accept-counter/{comment}', [CommentController::class, 'acceptCounter'])->name('jobs.accept-counter');
    Route::post('/jobs/{job}/increase-budget', [CommentController::class, 'increaseBudget'])->name('jobs.increase-budget');
    Route::post('/jobs/{job}/cancel', [JobController::class, 'cancel'])->name('jobs.cancel');

    // ============================================================
    // NEW WORKFLOW: Applications (worker apply, client manage)
    // ============================================================
    Route::post('/jobs/{job}/apply', [ApplicationController::class, 'store'])->name('jobs.apply');
    Route::post('/jobs/{job}/applications/{application}/shortlist', [ApplicationController::class, 'shortlist'])->name('applications.shortlist');
    Route::post('/jobs/{job}/applications/{application}/reject', [ApplicationController::class, 'reject'])->name('applications.reject');
    Route::post('/jobs/{job}/applications/{application}/counter', [ApplicationController::class, 'counter'])->name('applications.counter');
    Route::post('/jobs/{job}/applications/{application}/accept-counter', [ApplicationController::class, 'acceptCounter'])->name('applications.accept-counter');
    Route::post('/jobs/{job}/applications/{application}/withdraw', [ApplicationController::class, 'withdraw'])->name('applications.withdraw');
    Route::post('/jobs/{job}/applications/{application}/select', [ApplicationController::class, 'select'])->name('applications.select');

    // ============================================================
    // NEW WORKFLOW: Funding (escrow payment after worker selection)
    // ============================================================
    Route::get('/jobs/{job}/fund', [FundingController::class, 'show'])->name('jobs.fund');
    Route::post('/jobs/{job}/fund/wallet', [FundingController::class, 'fundFromWallet'])->name('jobs.fund.wallet');
    Route::post('/jobs/{job}/fund/external', [FundingController::class, 'fundExternal'])->name('jobs.fund.external');
    Route::get('/jobs/{job}/fund/wait', [FundingController::class, 'waitPage'])->name('jobs.fund.wait');
    Route::get('/jobs/{job}/fund/poll', [FundingController::class, 'poll'])->name('jobs.fund.poll');

    // ============================================================
    // NEW WORKFLOW: Completion (two-sided confirm, disputes, reviews)
    // ============================================================
    Route::post('/jobs/{job}/worker-accept', [CompletionController::class, 'workerAccept'])->name('jobs.worker.accept');
    Route::post('/jobs/{job}/worker-decline', [CompletionController::class, 'workerDecline'])->name('jobs.worker.decline');
    Route::post('/jobs/{job}/worker-submit', [CompletionController::class, 'workerSubmit'])->name('jobs.worker.submit');
    Route::post('/jobs/{job}/client-confirm', [CompletionController::class, 'clientConfirm'])->name('jobs.client.confirm');
    Route::post('/jobs/{job}/client-revision', [CompletionController::class, 'clientRevision'])->name('jobs.client.revision');
    Route::post('/jobs/{job}/client-dispute', [CompletionController::class, 'clientDispute'])->name('jobs.client.dispute');
    Route::post('/jobs/{job}/worker-dispute', [CompletionController::class, 'workerDispute'])->name('jobs.worker.dispute');
    Route::post('/jobs/{job}/review', [CompletionController::class, 'submitReview'])->name('jobs.review');

    // Dispute details page
    Route::get('/disputes/{dispute}', function (Dispute $dispute) {
        $dispute->load('job', 'raisedByUser', 'againstUser', 'messages.user');

        return view('disputes.show', compact('dispute'));
    })->name('disputes.show');

    // PRIVATE CHAT/MESSAGING
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{job}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{job}/send', [ChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/{job}/poll', [ChatController::class, 'poll'])->name('chat.poll');
    Route::get('/chat/unread-count', [ChatController::class, 'unreadCount'])->name('chat.unread');

    // MUHITAJI — kazi zangu + maombi (inbox)
    Route::get('/my/jobs', [MyJobsController::class, 'index'])->name('my.jobs');
    Route::get('/my/applications', [MyJobsController::class, 'applications'])->name('my.applications');

    // MFANYAKAZI — assigned list + actions
    Route::get('/mfanyakazi/assigned', [WorkerActionsController::class, 'assigned'])->name('mfanyakazi.assigned');
    Route::get('/mfanyakazi/my-applications', [WorkerApplicationsController::class, 'index'])->name('mfanyakazi.applications');
    Route::post('/mfanyakazi/jobs/{job}/accept', [WorkerActionsController::class, 'accept'])->name('mfanyakazi.jobs.accept');
    Route::post('/mfanyakazi/jobs/{job}/decline', [WorkerActionsController::class, 'decline'])->name('mfanyakazi.jobs.decline');
    Route::post('/mfanyakazi/jobs/{job}/complete', [WorkerActionsController::class, 'complete'])->name('mfanyakazi.jobs.complete');

    // WALLET: Deposit
    Route::get('/wallet/deposit', [WalletController::class, 'depositForm'])->name('wallet.deposit');
    Route::post('/wallet/deposit', [WalletController::class, 'depositSubmit'])->name('wallet.deposit.submit');
    Route::get('/wallet/deposit/{transaction}/wait', [WalletController::class, 'depositWait'])->name('wallet.deposit.wait');
    Route::get('/wallet/deposit/{transaction}/poll', [WalletController::class, 'depositPoll'])->name('wallet.deposit.poll');

    // WITHDRAWALS (ClickPesa Payout)
    Route::get('/withdraw', [WithdrawalController::class, 'requestForm'])->name('withdraw.form');
    Route::post('/withdraw/submit', [WalletController::class, 'withdrawSubmit'])->name('withdraw.submit');
    // ADMIN — Full Access Routes
    Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        // User Management
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::get('/users/{user}', [AdminController::class, 'userDetails'])->name('admin.user.details');
        Route::get('/users/{user}/dashboard', [AdminController::class, 'viewUserDashboard'])->name('admin.user.dashboard');
        Route::get('/users/{user}/monitor', [AdminController::class, 'monitorUser'])->name('admin.user.monitor');
        Route::get('/users/{user}/chats', [AdminController::class, 'userChats'])->name('admin.user.chats');

        // Job Management
        Route::get('/jobs', [AdminController::class, 'jobs'])->name('admin.jobs');
        Route::get('/jobs/{job}', [AdminController::class, 'jobDetails'])->name('admin.job.details');

        // Chat/Conversation Monitoring
        Route::get('/chats', [AdminController::class, 'allChats'])->name('admin.chats');
        Route::get('/chats/{job}', [AdminController::class, 'viewChat'])->name('admin.chat.view');

        // Commission & Fees Tracking
        Route::get('/commissions', [CommissionController::class, 'index'])->name('admin.commissions');

        // Analytics & Reports
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');

        // ADMIN IMPERSONATION - Login as any user
        Route::get('/impersonate/{user}', [AdminController::class, 'impersonate'])->name('admin.impersonate');
        Route::get('/stop-impersonate', [AdminController::class, 'stopImpersonate'])->name('admin.stop-impersonate');

        // ADMIN FULL CONTROL - User Management
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('admin.user.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.user.update');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.user.delete');
        Route::post('/users/{user}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('admin.user.toggle-status');

        // ADMIN FULL CONTROL - Job Management
        Route::post('/jobs/{job}/force-complete', [AdminController::class, 'forceCompleteJob'])->name('admin.job.force-complete');
        Route::post('/jobs/{job}/force-cancel', [AdminController::class, 'forceCancelJob'])->name('admin.job.force-cancel');

        // ADMIN FULL CONTROL - System Management
        Route::get('/system-logs', [AdminController::class, 'systemLogs'])->name('admin.system-logs');
        Route::get('/system-settings', [AdminController::class, 'systemSettings'])->name('admin.system-settings');
        Route::post('/system-settings', [AdminController::class, 'updateSystemSettings'])->name('admin.system-settings.update');
        Route::post('/apk/upload', [AdminController::class, 'uploadApk'])->name('admin.apk.upload');
        Route::post('/apk/scan', [AdminController::class, 'scanManualApk'])->name('admin.apk.scan');

        // Category Management
        Route::get('/categories', [AdminController::class, 'categories'])->name('admin.categories');
        Route::post('/categories', [AdminController::class, 'storeCategory'])->name('admin.categories.store');
        Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('admin.categories.update');
        Route::delete('/categories/{category}', [AdminController::class, 'deleteCategory'])->name('admin.categories.delete');

        // ADMIN FULL CONTROL - Communication
        Route::post('/users/{user}/send-message', [AdminController::class, 'sendMessageToUser'])->name('admin.user.send-message');

        // Broadcast Notifications
        Route::get('/broadcast', [AdminController::class, 'showBroadcast'])->name('admin.broadcast');
        Route::post('/broadcast', [AdminController::class, 'sendBroadcast'])->name('admin.broadcast.send');

        // Withdrawals
        Route::get('/withdrawals', [WithdrawalAdminController::class, 'index'])->name('admin.withdrawals');
        Route::post('/withdrawals/{withdrawal}/paid', [WithdrawalAdminController::class, 'markPaid'])->name('admin.withdrawals.paid');
        Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalAdminController::class, 'reject'])->name('admin.withdrawals.reject');

        // Completed Jobs by Workers (keeping legacy route)
        Route::get('/completed-jobs', function () {
            $workers = User::where('role', 'mfanyakazi')
                ->with([
                    'assignedJobs' => function ($query) {
                        $query->where('status', 'completed');
                    },
                ])
                ->withCount([
                    'assignedJobs as completed_jobs' => function ($query) {
                        $query->where('status', 'completed');
                    },
                ])
                ->orderBy('completed_jobs', 'desc')
                ->paginate(20);

            return view('admin.completed-jobs', compact('workers'));
        })->name('admin.completed-jobs');
    });
});

// ClickPesa webhook (no auth)
Route::post('/payment/clickpesa/webhook', [PaymentController::class, 'webhook'])->name('clickpesa.webhook');

// API routes for dashboard updates
Route::get('/api/dashboard-updates', [App\Http\Controllers\Api\DashboardController::class, 'updates'])->middleware('auth');

// Debug route for testing payment flow
Route::get('/debug/payment/{job}', function (Job $job) {
    $worker = $job->acceptedWorker;
    if (! $worker) {
        return response()->json(['error' => 'No worker found']);
    }

    $wallet = $worker->ensureWallet();
    $transactions = WalletTransaction::where('user_id', $worker->id)->latest()->take(5)->get();

    return response()->json([
        'job_id' => $job->id,
        'job_status' => $job->status,
        'job_amount' => $job->amount,
        'completion_code' => $job->completion_code,
        'worker_id' => $worker->id,
        'worker_name' => $worker->name,
        'wallet_balance' => $wallet->balance,
        'recent_transactions' => $transactions,
    ]);
})->middleware('auth');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->name('register.post');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.post');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

    // OTP-based forgot password flow
    Route::get('forgot-password-otp', [OtpPasswordResetController::class, 'showRequestForm'])
        ->name('password.otp.request');

    Route::post('forgot-password-otp', [OtpPasswordResetController::class, 'sendOtp'])
        ->name('password.otp.send');

    Route::get('verify-otp', [OtpPasswordResetController::class, 'showVerifyForm'])
        ->name('password.otp.verify.form');

    Route::post('verify-otp', [OtpPasswordResetController::class, 'verifyOtp'])
        ->name('password.otp.verify');

    Route::get('reset-password-otp', [OtpPasswordResetController::class, 'showResetForm'])
        ->name('password.otp.reset.form');

    Route::post('reset-password-otp', [OtpPasswordResetController::class, 'resetPassword'])
        ->name('password.otp.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Notifications
    Route::get('/notifications', function () {
        $user = Auth::user();
        $query = $user->notifications()->latest();
        if (request('filter') === 'unread') {
            $query->whereNull('read_at');
        }
        $notifications = $query->paginate(15)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();
        $totalCount = $user->notifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount', 'totalCount'));
    })->name('notifications.index');

    Route::post('/notifications/{id}/read', function (string $id) {
        $n = Auth::user()->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();

        return back()->with('success', 'Taarifa imewekwa kama imesomwa.');
    })->name('notifications.read');

    Route::post('/notifications/read-all', function () {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Taarifa zote zimewekwa kama zimesomwa.');
    })->name('notifications.readAll');
});
