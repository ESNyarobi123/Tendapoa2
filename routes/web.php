<?php

use App\Http\Controllers\{MyJobsController, WorkerActionsController, WithdrawalController};
use App\Http\Controllers\Admin\WithdrawalAdminController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    HomeController,
    DashboardController,
    JobController,
    PaymentController,
    FeedController,
    JobViewController,
    AuthController,
    AdminController
};
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;

// Serve storage files - this route handles all storage requests
// Must be before other routes to catch storage requests first
// This is critical for php artisan serve which may not handle symlinks properly
Route::match(['get', 'head', 'options'], '/storage/{path}', function ($path) {
    try {
        // Decode the path in case it's URL encoded
        $path = urldecode($path);

        // Remove any leading/trailing slashes
        $path = trim($path, '/');

        // Build the full file path
        $filePath = storage_path('app/public/' . $path);

        // Security: prevent directory traversal
        $normalizedPath = str_replace('\\', '/', realpath($filePath) ?: $filePath);
        $normalizedStorage = str_replace('\\', '/', realpath(storage_path('app/public')) ?: storage_path('app/public'));

        // Check if path is within storage directory
        if (strpos($normalizedPath, $normalizedStorage) !== 0) {
            \Log::warning('Storage access attempt outside public directory', [
                'path' => $path,
                'filePath' => $filePath,
                'normalizedPath' => $normalizedPath,
                'normalizedStorage' => $normalizedStorage
            ]);
            abort(403, 'Access denied');
        }

        // Check if file exists
        if (!file_exists($filePath)) {
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

            \Log::warning('Storage file not found - Route handler', [
                'requestedPath' => $path,
                'fullFilePath' => $filePath,
                'storageBase' => $storageBase,
                'storageBaseExists' => is_dir($storageBase),
                'storageBaseWritable' => is_dir($storageBase) ? is_writable($storageBase) : false,
                'jobsDir' => $jobsDir,
                'jobsDirExists' => is_dir($jobsDir),
                'jobsDirWritable' => is_dir($jobsDir) ? is_writable($jobsDir) : false,
                'jobsDirFilesCount' => is_dir($jobsDir) ? count(glob($jobsDir . '/*')) : 0,
                'sampleJobsFiles' => $jobsFiles,
                'symlinkExists' => is_link(public_path('storage')),
                'symlinkTarget' => is_link(public_path('storage')) ? readlink(public_path('storage')) : null
            ]);
            abort(404, 'File not found: ' . basename($path));
        }

        // Check if it's a file (not directory)
        if (!is_file($filePath)) {
            abort(404, 'Not a file');
        }

        // Check if readable
        if (!is_readable($filePath)) {
            \Log::warning('Storage file not readable', [
                'path' => $path,
                'permissions' => substr(sprintf('%o', fileperms($filePath)), -4)
            ]);
            abort(403, 'File not accessible');
        }

        // Determine MIME type
        $mimeType = mime_content_type($filePath);
        if (!$mimeType) {
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
        ]);
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        // Re-throw HTTP exceptions (404, 403, etc.)
        throw $e;
    } catch (\Exception $e) {
        \Log::error('Storage file serving error', [
            'path' => $path ?? 'unknown',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        abort(500, 'Error serving file: ' . $e->getMessage());
    }
})->where('path', '.*')->name('storage.serve');

// HOME / LANDING
Route::get('/', [HomeController::class, 'index'])->name('home');

// APK Download (Public)
Route::get('/download/app', [HomeController::class, 'downloadApp'])->name('app.download');

// Auth routes are loaded from auth.php

// AUTH-protected (🚫 hakuna 'role' middleware tena)
Route::middleware(['auth'])->group(function () {
    // PROFILE
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // MUHITAJI: create + pay (role check tutafanya ndani ya controller)
    Route::get('/jobs/create', [JobController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/{job}/wait', [JobController::class, 'wait'])->name('jobs.pay.wait');
    Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])->name('jobs.edit');
    Route::put('/jobs/{job}', [JobController::class, 'update'])->name('jobs.update');

    // MFANYAKAZI: create + pay jobs (role check tutafanya ndani ya controller)
    Route::get('/jobs/create-mfanyakazi', [JobController::class, 'createMfanyakazi'])->name('jobs.create-mfanyakazi');
    Route::post('/jobs-mfanyakazi', [JobController::class, 'storeMfanyakazi'])->name('jobs.store-mfanyakazi');

    // Poll status (generic)
    Route::get('/jobs/{job}/poll', [PaymentController::class, 'poll'])->name('jobs.pay.poll');

    // MFANYAKAZI feed + view + comment (role check ndani ya controller)
    Route::get('/feed', [FeedController::class, 'index'])->name('feed');
    Route::get('/jobs/{job}', [JobViewController::class, 'show'])->name('jobs.show');
    Route::post('/jobs/{job}/comment', [\App\Http\Controllers\CommentController::class, 'store'])->name('jobs.comment');

    // MUHITAJI accept/reject/counter (role check ndani ya controller)
    Route::post('/jobs/{job}/accept/{comment}', [\App\Http\Controllers\CommentController::class, 'accept'])->name('jobs.accept');
    Route::post('/jobs/{job}/reject/{comment}', [\App\Http\Controllers\CommentController::class, 'reject'])->name('jobs.reject');
    Route::post('/jobs/{job}/reply/{comment}', [\App\Http\Controllers\CommentController::class, 'reply'])->name('jobs.reply');
    Route::post('/jobs/{job}/counter/{comment}', [\App\Http\Controllers\CommentController::class, 'counterOffer'])->name('jobs.counter');
    Route::post('/jobs/{job}/accept-counter/{comment}', [\App\Http\Controllers\CommentController::class, 'acceptCounter'])->name('jobs.accept-counter');
    Route::post('/jobs/{job}/increase-budget', [\App\Http\Controllers\CommentController::class, 'increaseBudget'])->name('jobs.increase-budget');
    Route::post('/jobs/{job}/cancel', [JobController::class, 'cancel'])->name('jobs.cancel');

    // PRIVATE CHAT/MESSAGING
    Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{job}', [\App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{job}/send', [\App\Http\Controllers\ChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/{job}/poll', [\App\Http\Controllers\ChatController::class, 'poll'])->name('chat.poll');
    Route::get('/chat/unread-count', [\App\Http\Controllers\ChatController::class, 'unreadCount'])->name('chat.unread');


    // MUHITAJI — kazi zangu
    Route::get('/my/jobs', [MyJobsController::class, 'index'])->name('my.jobs');

    // MFANYAKAZI — assigned list + actions
    Route::get('/mfanyakazi/assigned', [WorkerActionsController::class, 'assigned'])->name('mfanyakazi.assigned');
    Route::post('/mfanyakazi/jobs/{job}/accept', [WorkerActionsController::class, 'accept'])->name('mfanyakazi.jobs.accept');
    Route::post('/mfanyakazi/jobs/{job}/decline', [WorkerActionsController::class, 'decline'])->name('mfanyakazi.jobs.decline');
    Route::post('/mfanyakazi/jobs/{job}/complete', [WorkerActionsController::class, 'complete'])->name('mfanyakazi.jobs.complete');

    // WITHDRAWALS
    Route::get('/withdraw', [WithdrawalController::class, 'requestForm'])->name('withdraw.form');
    Route::post('/withdraw/submit', [WithdrawalController::class, 'submit'])->name('withdraw.submit');
    // MFANYAKAZI — assigned list + actions
    Route::get('/mfanyakazi/assigned', [WorkerActionsController::class, 'assigned'])->name('mfanyakazi.assigned');
    Route::post('/mfanyakazi/jobs/{job}/accept', [WorkerActionsController::class, 'accept'])->name('mfanyakazi.jobs.accept');
    Route::post('/mfanyakazi/jobs/{job}/decline', [WorkerActionsController::class, 'decline'])->name('mfanyakazi.jobs.decline');
    Route::post('/mfanyakazi/jobs/{job}/complete', [WorkerActionsController::class, 'complete'])->name('mfanyakazi.jobs.complete');
    // ADMIN — Full Access Routes
    Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');

        // User Management
        Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
        Route::get('/users/{user}', [\App\Http\Controllers\AdminController::class, 'userDetails'])->name('admin.user.details');
        Route::get('/users/{user}/dashboard', [\App\Http\Controllers\AdminController::class, 'viewUserDashboard'])->name('admin.user.dashboard');
        Route::get('/users/{user}/monitor', [\App\Http\Controllers\AdminController::class, 'monitorUser'])->name('admin.user.monitor');

        // Job Management
        Route::get('/jobs', [\App\Http\Controllers\AdminController::class, 'jobs'])->name('admin.jobs');
        Route::get('/jobs/{job}', [\App\Http\Controllers\AdminController::class, 'jobDetails'])->name('admin.job.details');

        // Chat/Conversation Monitoring
        Route::get('/chats', [\App\Http\Controllers\AdminController::class, 'allChats'])->name('admin.chats');
        Route::get('/chats/{job}', [\App\Http\Controllers\AdminController::class, 'viewChat'])->name('admin.chat.view');

        // Commission & Fees Tracking
        Route::get('/commissions', [\App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('admin.commissions');

        // Analytics & Reports
        Route::get('/analytics', [\App\Http\Controllers\AdminController::class, 'analytics'])->name('admin.analytics');

        // ADMIN IMPERSONATION - Login as any user
        Route::get('/impersonate/{user}', [\App\Http\Controllers\AdminController::class, 'impersonate'])->name('admin.impersonate');
        Route::get('/stop-impersonate', [\App\Http\Controllers\AdminController::class, 'stopImpersonate'])->name('admin.stop-impersonate');

        // ADMIN FULL CONTROL - User Management
        Route::get('/users/{user}/edit', [\App\Http\Controllers\AdminController::class, 'editUser'])->name('admin.user.edit');
        Route::put('/users/{user}', [\App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.user.update');
        Route::delete('/users/{user}', [\App\Http\Controllers\AdminController::class, 'deleteUser'])->name('admin.user.delete');
        Route::post('/users/{user}/toggle-status', [\App\Http\Controllers\AdminController::class, 'toggleUserStatus'])->name('admin.user.toggle-status');

        // ADMIN FULL CONTROL - Job Management
        Route::post('/jobs/{job}/force-complete', [\App\Http\Controllers\AdminController::class, 'forceCompleteJob'])->name('admin.job.force-complete');
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

        // Withdrawals
        Route::get('/withdrawals', [WithdrawalAdminController::class, 'index'])->name('admin.withdrawals');
        Route::post('/withdrawals/{withdrawal}/paid', [WithdrawalAdminController::class, 'markPaid'])->name('admin.withdrawals.paid');
        Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalAdminController::class, 'reject'])->name('admin.withdrawals.reject');

        // Completed Jobs by Workers (keeping legacy route)
        Route::get('/completed-jobs', function () {
            $workers = \App\Models\User::where('role', 'mfanyakazi')
                ->with([
                    'assignedJobs' => function ($query) {
                        $query->where('status', 'completed');
                    }
                ])
                ->withCount([
                    'assignedJobs as completed_jobs' => function ($query) {
                        $query->where('status', 'completed');
                    }
                ])
                ->orderBy('completed_jobs', 'desc')
                ->paginate(20);
            return view('admin.completed-jobs', compact('workers'));
        })->name('admin.completed-jobs');
    });
});

// Zeno webhook (no auth)
Route::post('/payment/zeno/webhook', [PaymentController::class, 'webhook'])->name('zeno.webhook');

// API routes for dashboard updates
Route::get('/api/dashboard-updates', [\App\Http\Controllers\Api\DashboardController::class, 'updates'])->middleware('auth');

// Debug route for testing payment flow
Route::get('/debug/payment/{job}', function (\App\Models\Job $job) {
    $worker = $job->acceptedWorker;
    if (!$worker) {
        return response()->json(['error' => 'No worker found']);
    }

    $wallet = $worker->ensureWallet();
    $transactions = \App\Models\WalletTransaction::where('user_id', $worker->id)->latest()->take(5)->get();

    return response()->json([
        'job_id' => $job->id,
        'job_status' => $job->status,
        'job_amount' => $job->amount,
        'completion_code' => $job->completion_code,
        'worker_id' => $worker->id,
        'worker_name' => $worker->name,
        'wallet_balance' => $wallet->balance,
        'recent_transactions' => $transactions
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
        $notifications = \Illuminate\Support\Facades\Auth::user()->notifications()->paginate(15);
        return view('notifications.index', compact('notifications'));
    })->name('notifications.index');

    Route::post('/notifications/read-all', function () {
        \Illuminate\Support\Facades\Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Taarifa zote zimewekwa kama zimesomwa.');
    })->name('notifications.readAll');
});
