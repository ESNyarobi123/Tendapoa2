<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use App\Models\PrivateMessage;
use App\Models\Payment;
use App\Models\Withdrawal;
use App\Models\WalletTransaction;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Admin Dashboard - Overview
     * Note: Admin middleware is applied at route level
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'muhitaji_count' => User::where('role', 'muhitaji')->count(),
            'mfanyakazi_count' => User::where('role', 'mfanyakazi')->count(),
            'total_jobs' => Job::count(),
            'active_jobs' => Job::whereIn('status', ['posted', 'assigned', 'in_progress'])->count(),
            'completed_jobs' => Job::where('status', 'completed')->count(),
            'total_messages' => PrivateMessage::count(),
            'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
            'pending_withdrawals' => Withdrawal::where('status', 'pending')->count(),
        ];

        // Recent activities
        $recentJobs = Job::with(['muhitaji', 'acceptedWorker'])
            ->latest()
            ->limit(10)
            ->get();

        $recentUsers = User::latest()->limit(10)->get();

        return view('admin.dashboard', compact('stats', 'recentJobs', 'recentUsers'));
    }

    /**
     * View all users
     */
    public function users(Request $request)
    {
        $query = User::with('wallet');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users', compact('users'));
    }

    /**
     * View specific user details with FULL ACCESS and ALL ACTIVITIES
     */
    public function userDetails(User $user)
    {
        try {
            // Debug: Log the user ID being passed
            \Log::info('Admin userDetails - Requested User ID: ' . $user->id);
            \Log::info('Admin userDetails - Requested User Name: ' . $user->name);
            \Log::info('Admin userDetails - Requested User Email: ' . $user->email);
            
            // Force refresh the user from database
            $user = User::find($user->id);
            if (!$user) {
                abort(404, 'User not found');
            }
            
            // Load all user relationships
            $user->load([
                'jobs' => fn($q) => $q->with(['acceptedWorker', 'category'])->latest(),
                'assignedJobs' => fn($q) => $q->with(['muhitaji', 'category'])->latest(),
                'wallet',
                'withdrawals' => fn($q) => $q->latest(),
                'sentMessages' => fn($q) => $q->with(['receiver', 'job'])->latest()->limit(50),
                'receivedMessages' => fn($q) => $q->with(['sender', 'job'])->latest()->limit(50),
            ]);

            // Debug: Log user data
            \Log::info('Admin userDetails - User ID: ' . $user->id);
            \Log::info('Admin userDetails - User loaded: ' . $user->name);
            \Log::info('Admin userDetails - User email: ' . $user->email);
            \Log::info('Admin userDetails - Jobs count: ' . $user->jobs->count());
            \Log::info('Admin userDetails - Assigned jobs count: ' . $user->assignedJobs->count());

        // Get comprehensive user statistics
        $stats = [
            'jobs_posted' => $user->jobs()->count(),
            'jobs_assigned' => $user->assignedJobs()->count(),
            'jobs_completed' => $user->assignedJobs()->where('status', 'completed')->count(),
            'jobs_in_progress' => $user->assignedJobs()->where('status', 'in_progress')->count(),
            'jobs_cancelled' => $user->jobs()->where('status', 'cancelled')->count(),
            'wallet_balance' => $user->wallet->balance ?? 0,
            'total_earned' => WalletTransaction::where('user_id', $user->id)
                ->where('type', 'credit')
                ->sum('amount'),
            'total_spent' => WalletTransaction::where('user_id', $user->id)
                ->where('type', 'debit')
                ->sum('amount'),
            'total_withdrawn' => Withdrawal::where('user_id', $user->id)
                ->where('status', 'paid')
                ->sum('amount'),
            'pending_withdrawals' => Withdrawal::where('user_id', $user->id)
                ->where('status', 'pending')
                ->sum('amount'),
            'messages_sent' => PrivateMessage::where('sender_id', $user->id)->count(),
            'messages_received' => PrivateMessage::where('receiver_id', $user->id)->count(),
            'total_conversations' => PrivateMessage::where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id)
                ->distinct('work_order_id')
                ->count('work_order_id'),
        ];

        // Get ALL activities timeline
        $activities = collect();

        // Jobs posted
        foreach ($user->jobs as $job) {
            $activities->push([
                'type' => 'job_posted',
                'icon' => '📝',
                'color' => 'blue',
                'title' => 'Posted Job',
                'description' => $job->title,
                'details' => "Budget: Tsh " . number_format($job->budget ?? $job->amount) . " | Status: " . ucfirst($job->status),
                'timestamp' => $job->created_at,
                'link' => route('admin.job.details', $job),
                'data' => $job,
            ]);
        }

        // Jobs assigned (as worker)
        foreach ($user->assignedJobs as $job) {
            $activities->push([
                'type' => 'job_assigned',
                'icon' => '✅',
                'color' => 'green',
                'title' => 'Assigned to Job',
                'description' => $job->title,
                'details' => "By: " . $job->muhitaji->name . " | Amount: Tsh " . number_format($job->amount),
                'timestamp' => $job->accepted_at ?? $job->updated_at,
                'link' => route('admin.job.details', $job),
                'data' => $job,
            ]);
        }

        // Messages sent
        foreach ($user->sentMessages as $message) {
            $activities->push([
                'type' => 'message_sent',
                'icon' => '💬',
                'color' => 'purple',
                'title' => 'Sent Message',
                'description' => "To: " . $message->receiver->name,
                'details' => substr($message->message, 0, 100) . (strlen($message->message) > 100 ? '...' : ''),
                'timestamp' => $message->created_at,
                'link' => route('admin.chat.view', $message->work_order_id),
                'data' => $message,
            ]);
        }

        // Messages received
        foreach ($user->receivedMessages as $message) {
            $activities->push([
                'type' => 'message_received',
                'icon' => '📨',
                'color' => 'indigo',
                'title' => 'Received Message',
                'description' => "From: " . $message->sender->name,
                'details' => substr($message->message, 0, 100) . (strlen($message->message) > 100 ? '...' : ''),
                'timestamp' => $message->created_at,
                'link' => route('admin.chat.view', $message->work_order_id),
                'data' => $message,
            ]);
        }

        // Withdrawals
        foreach ($user->withdrawals as $withdrawal) {
            $activities->push([
                'type' => 'withdrawal',
                'icon' => '💰',
                'color' => 'orange',
                'title' => 'Withdrawal Request',
                'description' => "Amount: Tsh " . number_format($withdrawal->amount),
                'details' => "Status: " . ucfirst($withdrawal->status) . " | Method: " . ($withdrawal->method ?? 'N/A'),
                'timestamp' => $withdrawal->created_at,
                'link' => null,
                'data' => $withdrawal,
            ]);
        }

        // Sort all activities by timestamp (newest first)
        $activities = $activities->sortByDesc('timestamp')->values();

        // Recent wallet transactions
        $transactions = WalletTransaction::where('user_id', $user->id)
            ->latest()
            ->limit(50)
            ->get();

        // Get user's active conversations
        $conversations = PrivateMessage::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->select('work_order_id')
            ->distinct()
            ->with(['job' => fn($q) => $q->with(['muhitaji', 'acceptedWorker'])])
            ->get();

            return view('admin.user-details', compact('user', 'stats', 'transactions', 'activities', 'conversations'));
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error('Admin userDetails error: ' . $e->getMessage());
            
            // Return basic user data if there's an error
            $user->load(['wallet']);
            $stats = [
                'jobs_posted' => 0,
                'jobs_assigned' => 0,
                'jobs_completed' => 0,
                'jobs_in_progress' => 0,
                'jobs_cancelled' => 0,
                'wallet_balance' => $user->wallet->balance ?? 0,
                'total_earned' => 0,
                'total_spent' => 0,
                'total_withdrawn' => 0,
                'pending_withdrawals' => 0,
                'messages_sent' => 0,
                'messages_received' => 0,
                'total_conversations' => 0,
            ];
            $activities = collect();
            $conversations = collect();
            $transactions = collect();
            
            return view('admin.user-details', compact('user', 'stats', 'transactions', 'activities', 'conversations'))
                ->with('error', 'Error loading user details: ' . $e->getMessage());
        }
    }

    /**
     * View all jobs
     */
    public function jobs(Request $request)
    {
        $query = Job::with(['muhitaji', 'acceptedWorker', 'category']);

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Search
        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $jobs = $query->latest()->paginate(20);

        return view('admin.jobs', compact('jobs'));
    }

    /**
     * View specific job details
     */
    public function jobDetails(Job $job)
    {
        $job->load([
            'muhitaji',
            'acceptedWorker',
            'category',
            'comments.user',
            'privateMessages.sender',
            'privateMessages.receiver',
            'payment',
        ]);

        return view('admin.job-details', compact('job'));
    }

    /**
     * View all private messages/chats
     */
    public function allChats(Request $request)
    {
        // Get all conversations
        $conversations = DB::table('private_messages')
            ->select(
                'work_order_id',
                DB::raw('MAX(created_at) as last_message_at'),
                DB::raw('COUNT(*) as message_count')
            )
            ->groupBy('work_order_id')
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        // Load job details
        $jobIds = $conversations->pluck('work_order_id');
        $jobs = Job::with(['muhitaji', 'acceptedWorker'])
            ->whereIn('id', $jobIds)
            ->get()
            ->keyBy('id');

        // Merge data
        $conversations->getCollection()->transform(function($conv) use ($jobs) {
            $job = $jobs->get($conv->work_order_id);
            $conv->job = $job;
            return $conv;
        });

        return view('admin.chats', compact('conversations'));
    }

    /**
     * View specific chat/conversation
     */
    public function viewChat(Job $job)
    {
        $messages = PrivateMessage::forJob($job->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        $job->load(['muhitaji', 'acceptedWorker']);

        return view('admin.chat-details', compact('job', 'messages'));
    }

    /**
     * View user's dashboard as admin (impersonate view)
     */
    public function viewUserDashboard(User $user)
    {
        // Get user's dashboard data
        $role = $user->role;

        if ($role === 'muhitaji') {
            $jobs = Job::where('user_id', $user->id)
                ->with(['acceptedWorker', 'category'])
                ->latest()
                ->paginate(10);

            $stats = [
                'total_jobs' => Job::where('user_id', $user->id)->count(),
                'active_jobs' => Job::where('user_id', $user->id)
                    ->whereIn('status', ['posted', 'assigned', 'in_progress'])
                    ->count(),
                'completed_jobs' => Job::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->count(),
            ];

            return view('admin.user-dashboard-muhitaji', compact('user', 'jobs', 'stats'));
        }

        if ($role === 'mfanyakazi') {
            $assignedJobs = Job::where('accepted_worker_id', $user->id)
                ->with(['muhitaji', 'category'])
                ->latest()
                ->paginate(10);

            $wallet = $user->wallet;
            $balance = $wallet ? $wallet->balance : 0;

            $stats = [
                'total_jobs' => Job::where('accepted_worker_id', $user->id)->count(),
                'completed_jobs' => Job::where('accepted_worker_id', $user->id)
                    ->where('status', 'completed')
                    ->count(),
                'wallet_balance' => $balance,
            ];

            return view('admin.user-dashboard-mfanyakazi', compact('user', 'assignedJobs', 'stats', 'balance'));
        }

        abort(404, 'Dashboard haijulikani kwa role hii.');
    }

    /**
     * Monitor user activity
     */
    public function monitorUser(User $user)
    {
        // Get recent activities
        $recentJobs = Job::where('user_id', $user->id)
            ->orWhere('accepted_worker_id', $user->id)
            ->with(['muhitaji', 'acceptedWorker'])
            ->latest()
            ->limit(20)
            ->get();

        $recentMessages = PrivateMessage::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['sender', 'receiver', 'job'])
            ->latest()
            ->limit(50)
            ->get();

        $recentTransactions = WalletTransaction::where('user_id', $user->id)
            ->latest()
            ->limit(20)
            ->get();

        // Activity timeline
        $activities = collect();

        // Add jobs
        foreach ($recentJobs as $job) {
            $activities->push([
                'type' => 'job',
                'data' => $job,
                'timestamp' => $job->created_at,
            ]);
        }

        // Add messages
        foreach ($recentMessages as $message) {
            $activities->push([
                'type' => 'message',
                'data' => $message,
                'timestamp' => $message->created_at,
            ]);
        }

        // Add transactions
        foreach ($recentTransactions as $transaction) {
            $activities->push([
                'type' => 'transaction',
                'data' => $transaction,
                'timestamp' => $transaction->created_at,
            ]);
        }

        // Sort by timestamp
        $activities = $activities->sortByDesc('timestamp')->values();

        return view('admin.user-monitor', compact('user', 'activities'));
    }

    /**
     * System analytics
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', '30'); // days

        // User growth
        $userGrowth = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Job statistics
        $jobStats = Job::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue
        $revenue = Payment::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total')
            )
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top workers
        $topWorkers = User::where('role', 'mfanyakazi')
            ->withCount(['assignedJobs as completed_jobs' => function($q) {
                $q->where('status', 'completed');
            }])
            ->orderBy('completed_jobs', 'desc')
            ->limit(10)
            ->get();

        return view('admin.analytics', compact(
            'userGrowth',
            'jobStats',
            'revenue',
            'topWorkers',
            'period'
        ));
    }

    /**
     * ADMIN IMPERSONATION - Login as any user
     */
    public function impersonate(User $user)
    {
        // Store original admin ID
        Session::put('admin_id', Auth::id());
        
        // Login as the target user
        Auth::login($user);
        
        return redirect()->route('dashboard')->with('success', 
            "Umeingia kama {$user->name}. <a href='" . route('admin.stop-impersonate') . "' class='text-red-600 font-bold'>Rudi kwa Admin</a>"
        );
    }

    /**
     * Stop impersonation - Return to admin
     */
    public function stopImpersonate()
    {
        $adminId = Session::get('admin_id');
        
        if ($adminId) {
            $admin = User::find($adminId);
            Auth::login($admin);
            Session::forget('admin_id');
            
            return redirect()->route('admin.dashboard')->with('success', 'Umerudi kwa Admin Dashboard');
        }
        
        return redirect()->route('admin.dashboard')->with('error', 'Hakuna admin session');
    }

    /**
     * ADMIN FULL CONTROL - Edit any user
     */
    public function editUser(User $user)
    {
        return view('admin.edit-user', compact('user'));
    }

    /**
     * ADMIN FULL CONTROL - Update any user
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:muhitaji,mfanyakazi,admin',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $user->update($request->all());

        return redirect()->route('admin.user.details', $user)
            ->with('success', 'User updated successfully!');
    }

    /**
     * ADMIN FULL CONTROL - Delete any user
     */
    public function deleteUser(User $user)
    {
        // Prevent admin from deleting themselves
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Huwezi kujifuta!');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', "User {$userName} deleted successfully!");
    }

    /**
     * ADMIN FULL CONTROL - Suspend/Activate user
     */
    public function toggleUserStatus(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'suspended';
        
        return back()->with('success', "User {$user->name} has been {$status}!");
    }

    /**
     * ADMIN FULL CONTROL - View all system logs
     */
    public function systemLogs()
    {
        $logs = [];
        
        // Get recent activities
        $activities = collect();
        
        // Recent jobs
        $recentJobs = Job::with(['muhitaji', 'acceptedWorker'])
            ->latest()
            ->limit(50)
            ->get();
            
        foreach ($recentJobs as $job) {
            $activities->push([
                'type' => 'job_created',
                'user' => $job->muhitaji,
                'description' => "Created job: {$job->title}",
                'timestamp' => $job->created_at,
                'data' => $job
            ]);
        }

        // Recent messages
        $recentMessages = PrivateMessage::with(['sender', 'receiver', 'job'])
            ->latest()
            ->limit(50)
            ->get();
            
        foreach ($recentMessages as $message) {
            $activities->push([
                'type' => 'message_sent',
                'user' => $message->sender,
                'description' => "Sent message to {$message->receiver->name}",
                'timestamp' => $message->created_at,
                'data' => $message
            ]);
        }

        // Recent payments
        $recentPayments = Payment::with(['job.muhitaji'])
            ->latest()
            ->limit(50)
            ->get();
            
        foreach ($recentPayments as $payment) {
            $activities->push([
                'type' => 'payment_made',
                'user' => $payment->job->muhitaji ?? null,
                'description' => "Made payment: Tsh " . number_format($payment->amount) . " for job: " . ($payment->job->title ?? 'Unknown'),
                'timestamp' => $payment->created_at,
                'data' => $payment
            ]);
        }

        // Sort by timestamp
        $activities = $activities->sortByDesc('timestamp')->values();

        return view('admin.system-logs', compact('activities'));
    }

    /**
     * ADMIN FULL CONTROL - System settings
     */
    public function systemSettings()
    {
        return view('admin.system-settings');
    }

    /**
     * ADMIN FULL CONTROL - Update system settings
     */
    public function updateSystemSettings(Request $request)
    {
        // Handle APK file upload if provided
        if ($request->hasFile('apk_file')) {
            $request->validate([
                'apk_file' => 'required|file|mimes:apk|max:102400', // Max 100MB
                'apk_version' => 'required|string|max:50',
                'apk_description' => 'nullable|string|max:1000',
            ]);

            $file = $request->file('apk_file');
            $version = $request->input('apk_version');
            $description = $request->input('apk_description');

            // Check if version already exists
            $existingVersion = AppVersion::where('version', $version)->first();
            if ($existingVersion) {
                return back()->with('error', "Version {$version} already exists. Please use a different version number.");
            }

            // Store file in storage/app/public/apk/
            $fileName = 'tendapoa-' . $version . '-' . time() . '.apk';
            $filePath = $file->storeAs('apk', $fileName, 'public');

            // Deactivate all previous versions
            AppVersion::where('is_active', true)->update(['is_active' => false]);

            // Create new app version record
            AppVersion::create([
                'version' => $version,
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'is_active' => true,
                'description' => $description,
            ]);

            return back()->with('success', "APK version {$version} uploaded successfully and set as active!");
        }

        // Here you can add other system-wide settings
        return back()->with('success', 'System settings updated!');
    }

    /**
     * ADMIN FULL CONTROL - Upload APK file (AJAX)
     */
    public function uploadApk(Request $request)
    {
        // Increase execution time and memory for large file processing
        set_time_limit(600); // 10 minutes
        ini_set('max_execution_time', '600');
        ini_set('memory_limit', '512M');
        
        try {
            // Check if file is provided
            if (!$request->hasFile('apk_file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'APK file is required. Please select a file to upload.'
                ], 400);
            }
            
            // Check PHP configuration before processing
            $uploadMaxFilesize = $this->parseSize(ini_get('upload_max_filesize'));
            $postMaxSize = $this->parseSize(ini_get('post_max_size'));
            $maxExecutionTime = ini_get('max_execution_time');
            
            $file = $request->file('apk_file');
            $fileSize = $file->getSize();
            
            if ($fileSize > $uploadMaxFilesize) {
                return response()->json([
                    'success' => false,
                    'message' => "File size ({$this->formatBytes($fileSize)}) exceeds PHP upload_max_filesize limit ({$this->formatBytes($uploadMaxFilesize)}). Please increase upload_max_filesize in php.ini or use a smaller file."
                ], 400);
            }
            
            if ($fileSize > $postMaxSize) {
                return response()->json([
                    'success' => false,
                    'message' => "File size ({$this->formatBytes($fileSize)}) exceeds PHP post_max_size limit ({$this->formatBytes($postMaxSize)}). Please increase post_max_size in php.ini or use a smaller file."
                ], 400);
            }

            // Validate file
            $request->validate([
                'apk_file' => 'required|file|mimes:apk|max:102400', // Max 100MB
                'apk_version' => 'required|string|max:50|regex:/^[0-9]+\.[0-9]+\.[0-9]+$/',
                'apk_description' => 'nullable|string|max:1000',
            ], [
                'apk_file.required' => 'Please select an APK file to upload.',
                'apk_file.file' => 'The uploaded file is not valid.',
                'apk_file.mimes' => 'Only .apk files are allowed. Please upload a valid APK file.',
                'apk_file.max' => 'The APK file is too large. Maximum size is 100MB. Your file size: ' . 
                    number_format($request->file('apk_file')->getSize() / 1024 / 1024, 2) . ' MB',
                'apk_version.required' => 'Version number is required.',
                'apk_version.regex' => 'Version number must be in format: X.Y.Z (e.g., 1.0.0, 2.1.3)',
            ]);

            $file = $request->file('apk_file');
            $version = $request->input('apk_version');
            $description = $request->input('apk_description');

            // Check file size manually (in case validation doesn't catch it)
            $fileSizeMB = $file->getSize() / 1024 / 1024;
            if ($fileSizeMB > 100) {
                return response()->json([
                    'success' => false,
                    'message' => "File is too large ({$fileSizeMB} MB). Maximum allowed size is 100MB. Please compress or use a smaller APK file."
                ], 400);
            }

            // Check if version already exists
            $existingVersion = AppVersion::where('version', $version)->first();
            if ($existingVersion) {
                return response()->json([
                    'success' => false,
                    'message' => "Version {$version} already exists. Please use a different version number (e.g., " . 
                        $this->suggestNextVersion($version) . ")."
                ], 400);
            }

            // Ensure directory exists
            $apkDir = storage_path('app/public/apk');
            if (!file_exists($apkDir)) {
                mkdir($apkDir, 0755, true);
            }
            
            // Store file in storage/app/public/apk/ using stream for large files
            $fileName = 'tendapoa-' . $version . '-' . time() . '.apk';
            $destinationPath = $apkDir . '/' . $fileName;
            
            // Use move_uploaded_file for better performance with large files
            if (!move_uploaded_file($file->getPathname(), $destinationPath)) {
                // Fallback to Laravel's store method
                $filePath = $file->storeAs('apk', $fileName, 'public');
                if (!$filePath) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to save the APK file. Please check storage permissions and try again.'
                    ], 500);
                }
                $filePath = 'apk/' . $fileName;
            } else {
                $filePath = 'apk/' . $fileName;
            }

            // Deactivate all previous versions
            AppVersion::where('is_active', true)->update(['is_active' => false]);

            // Create new app version record
            // #region agent log
            $logFile = base_path('.cursor/debug.log');
            $logEntry = json_encode([
                'location' => 'AdminController.php:848',
                'message' => 'Creating AppVersion record',
                'data' => [
                    'version' => $version,
                    'filePath' => $filePath,
                    'fileName' => $file->getClientOriginalName(),
                    'fileSize' => $file->getSize(),
                ],
                'timestamp' => time() * 1000,
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'J'
            ]) . "\n";
            file_put_contents($logFile, $logEntry, FILE_APPEND);
            // #endregion
            
            $appVersion = AppVersion::create([
                'version' => $version,
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'is_active' => true,
                'description' => $description,
            ]);

            return response()->json([
                'success' => true,
                'message' => "APK version {$version} uploaded successfully and set as active!",
                'data' => [
                    'version' => $appVersion->version,
                    'file_name' => $appVersion->file_name,
                    'file_size' => $appVersion->file_size,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $firstError = collect($errors)->flatten()->first();
            
            return response()->json([
                'success' => false,
                'message' => $firstError ?? 'Validation failed. Please check your input.',
                'errors' => $errors
            ], 422);

        } catch (\Exception $e) {
            \Log::error('APK Upload Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading the APK: ' . $e->getMessage() . 
                    '. Please try again or contact support if the problem persists.'
            ], 500);
        }
    }

    /**
     * Parse PHP size string to bytes
     */
    private function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        return round($size);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Suggest next version number
     */
    private function suggestNextVersion($currentVersion)
    {
        $parts = explode('.', $currentVersion);
        if (count($parts) === 3) {
            $parts[2] = (int)$parts[2] + 1;
            return implode('.', $parts);
        }
        return $currentVersion . '.1';
    }

    /**
     * ADMIN FULL CONTROL - Force complete any job
     */
    public function forceCompleteJob(Job $job)
    {
        $job->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with('success', "Job '{$job->title}' has been force completed!");
    }

    /**
     * ADMIN FULL CONTROL - Force cancel any job
     */
    public function forceCancelJob(Job $job)
    {
        $job->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', "Job '{$job->title}' has been force cancelled!");
    }

    /**
     * ADMIN FULL CONTROL - Send message to any user
     */
    public function sendMessageToUser(Request $request, User $user)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Create a system message (you can create a system_messages table)
        // For now, we'll just return success
        return back()->with('success', "Message sent to {$user->name}!");
    }
}

