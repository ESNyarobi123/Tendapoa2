<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use App\Models\Category;
use App\Models\PrivateMessage;
use App\Models\Payment;
use App\Models\Withdrawal;
use App\Models\WalletTransaction;
use App\Models\AppVersion;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Admin Dashboard — overview (takwimu halisi kwa mfumo wa escrow + hali za zamani).
     */
    public function dashboard()
    {
        $terminal = [Job::S_COMPLETED, Job::S_CANCELLED, Job::S_EXPIRED, Job::S_REFUNDED, 'completed', 'cancelled'];

        $stats = [
            'total_users' => User::count(),
            'muhitaji_count' => User::where('role', 'muhitaji')->count(),
            'mfanyakazi_count' => User::where('role', 'mfanyakazi')->count(),
            'admin_count' => User::where('role', 'admin')->count(),
            'total_jobs' => Job::count(),
            'active_jobs' => Job::whereNotIn('status', $terminal)->count(),
            'completed_jobs' => Job::whereIn('status', [Job::S_COMPLETED, 'completed'])->count(),
            'open_jobs' => Job::where('status', Job::S_OPEN)->count(),
            'awaiting_payment_jobs' => Job::where('status', Job::S_AWAITING_PAYMENT)->count(),
            'escrow_pipeline_jobs' => Job::whereIn('status', [Job::S_FUNDED, Job::S_IN_PROGRESS, Job::S_SUBMITTED])->count(),
            'disputed_jobs' => Job::where('status', Job::S_DISPUTED)->count(),
            'cancelled_jobs' => Job::whereIn('status', [Job::S_CANCELLED, 'cancelled'])->count(),
            'total_messages' => PrivateMessage::count(),
            'payments_completed_sum' => (int) Payment::where('status', 'COMPLETED')->sum('amount'),
            'pending_withdrawals' => Withdrawal::where('status', 'PROCESSING')->count(),
            'platform_fees_sum' => (int) Job::whereIn('status', [Job::S_COMPLETED, 'completed'])->sum('platform_fee_amount'),
        ];

        $stats['completion_rate_pct'] = $stats['total_jobs'] > 0
            ? (int) round(($stats['completed_jobs'] / $stats['total_jobs']) * 100)
            : 0;

        $usersLast30 = User::where('created_at', '>=', now()->subDays(30))->count();
        $usersPrev30 = User::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->count();
        $stats['users_trend_pct'] = $usersPrev30 > 0
            ? (int) round((($usersLast30 - $usersPrev30) / $usersPrev30) * 100)
            : null;

        $jobsCreatedLast30 = Job::where('created_at', '>=', now()->subDays(30))->count();
        $jobsCreatedPrev30 = Job::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->count();
        $stats['jobs_created_trend_pct'] = $jobsCreatedPrev30 > 0
            ? (int) round((($jobsCreatedLast30 - $jobsCreatedPrev30) / $jobsCreatedPrev30) * 100)
            : null;

        $recentJobs = Job::with(['muhitaji', 'acceptedWorker', 'category'])
            ->latest()
            ->limit(10)
            ->get();

        $recentUsers = User::latest()->limit(10)->get();

        $recentWithdrawals = Withdrawal::with('user')->latest()->limit(10)->get();

        $topWorkers = User::where('role', 'mfanyakazi')
            ->withCount([
                'assignedJobs as completed_jobs_count' => fn ($q) => $q->whereIn('status', [Job::S_COMPLETED, 'completed']),
            ])
            ->orderByDesc('completed_jobs_count')
            ->limit(5)
            ->get();

        $pipelineMonitorJobs = Job::with(['muhitaji', 'acceptedWorker'])
            ->whereIn('status', [
                Job::S_FUNDED,
                Job::S_IN_PROGRESS,
                Job::S_SUBMITTED,
                'assigned',
                'in_progress',
                'ready_for_confirmation',
            ])
            ->latest()
            ->limit(8)
            ->get();

        $revenueLabels = [];
        $revenueData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenueLabels[] = $date->format('M d');
            $d = $date->format('Y-m-d');
            $sum = Job::whereIn('status', [Job::S_COMPLETED, 'completed'])
                ->where(function ($q) use ($d) {
                    $q->whereDate('completed_at', $d)
                        ->orWhere(function ($q2) use ($d) {
                            $q2->whereNull('completed_at')->whereDate('updated_at', $d);
                        });
                })
                ->sum('price');
            $revenueData[] = (int) $sum;
        }

        return view('admin.dashboard', compact(
            'stats',
            'recentJobs',
            'recentUsers',
            'recentWithdrawals',
            'topWorkers',
            'pipelineMonitorJobs',
            'revenueLabels',
            'revenueData',
        ));
    }

    /**
     * View all users
     */
    public function users(Request $request)
    {
        $query = User::with('wallet');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
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
            $user = User::find($user->id);
            if (! $user) {
                abort(404, 'User not found');
            }

            $user->load([
                'jobs' => fn ($q) => $q->with(['acceptedWorker', 'category'])->latest(),
                'assignedJobs' => fn ($q) => $q->with(['muhitaji', 'category'])->latest(),
                'wallet',
                'withdrawals' => fn ($q) => $q->latest(),
                'sentMessages' => fn ($q) => $q->with(['receiver', 'job'])->latest()->limit(50),
                'receivedMessages' => fn ($q) => $q->with(['sender', 'job'])->latest()->limit(50),
            ]);

            $wallet = $user->wallet;

            $stats = [
                'jobs_posted' => $user->jobs()->count(),
                'jobs_assigned' => $user->assignedJobs()->count(),
                'jobs_completed' => $user->assignedJobs()->whereIn('status', [Job::S_COMPLETED, 'completed'])->count(),
                'jobs_in_progress' => $user->assignedJobs()->whereIn('status', [
                    Job::S_FUNDED,
                    Job::S_IN_PROGRESS,
                    Job::S_SUBMITTED,
                    'in_progress',
                    'assigned',
                    'ready_for_confirmation',
                ])->count(),
                'jobs_cancelled' => $user->jobs()->whereIn('status', [Job::S_CANCELLED, 'cancelled'])->count(),
                'wallet_balance' => $wallet->balance ?? 0,
                'wallet_available' => $wallet ? $wallet->available_balance : 0,
                'total_earned' => WalletTransaction::where('user_id', $user->id)
                    ->where('type', 'EARN')
                    ->where('amount', '>', 0)
                    ->sum('amount'),
                'total_spent' => abs((int) WalletTransaction::where('user_id', $user->id)
                    ->where('amount', '<', 0)
                    ->sum('amount')),
                'total_withdrawn' => Withdrawal::where('user_id', $user->id)
                    ->where('status', 'PAID')
                    ->sum('amount'),
                'pending_withdrawals' => Withdrawal::where('user_id', $user->id)
                    ->where('status', 'PROCESSING')
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
                    'details' => 'Bei: TZS '.number_format($job->price ?? $job->agreed_amount ?? $job->budget ?? 0).' | Status: '.$job->status,
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
                    'details' => 'Mteja: '.($job->muhitaji->name ?? '—').' | TZS '.number_format($job->agreed_amount ?? $job->price ?? 0),
                    'timestamp' => $job->accepted_by_worker_at ?? $job->updated_at,
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

        $filterUser = null;
        if ($uid = $request->get('user')) {
            $filterUser = User::find($uid);
            if ($filterUser) {
                $query->where(function ($q) use ($filterUser) {
                    $q->where('user_id', $filterUser->id)
                        ->orWhere('accepted_worker_id', $filterUser->id);
                });
            }
        }

        $jobs = $query->latest()->paginate(20)->withQueryString();

        return view('admin.jobs', compact('jobs', 'filterUser'));
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
            ->paginate(20)
            ->withQueryString();

        // Load job details
        $jobIds = $conversations->pluck('work_order_id');
        $jobs = Job::with(['muhitaji', 'acceptedWorker'])
            ->whereIn('id', $jobIds)
            ->get()
            ->keyBy('id');

        // Merge data
        $conversations->getCollection()->transform(function ($conv) use ($jobs) {
            $job = $jobs->get($conv->work_order_id);
            $conv->job = $job;
            return $conv;
        });

        return view('admin.chats', compact('conversations'));
    }

    /**
     * Mazungumzo ya faragha yanayohusisha mtumiaji (muhitaji au mfanyakazi kwenye kazi).
     */
    public function userChats(User $user)
    {
        $conversations = DB::table('private_messages')
            ->join('work_orders', 'private_messages.work_order_id', '=', 'work_orders.id')
            ->where(function ($q) use ($user) {
                $q->where('work_orders.user_id', $user->id)
                    ->orWhere('work_orders.accepted_worker_id', $user->id);
            })
            ->select(
                'private_messages.work_order_id',
                DB::raw('MAX(private_messages.created_at) as last_message_at'),
                DB::raw('COUNT(*) as message_count')
            )
            ->groupBy('private_messages.work_order_id')
            ->orderBy('last_message_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $jobIds = $conversations->pluck('work_order_id');
        $jobs = Job::with(['muhitaji', 'acceptedWorker'])
            ->whereIn('id', $jobIds)
            ->get()
            ->keyBy('id');

        $conversations->getCollection()->transform(function ($conv) use ($jobs) {
            $conv->job = $jobs->get($conv->work_order_id);

            return $conv;
        });

        $chatsForUser = $user;

        return view('admin.chats', compact('conversations', 'chatsForUser'));
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
        $period = $request->get('period', '30');
        $startDateStr = $request->get('start_date');
        $endDateStr = $request->get('end_date');

        if ($startDateStr && $endDateStr) {
            $startDate = \Carbon\Carbon::parse($startDateStr)->startOfDay();
            $endDate = \Carbon\Carbon::parse($endDateStr)->endOfDay();
            $period = $startDate->diffInDays($endDate);
        } else {
            $startDate = now()->subDays($period)->startOfDay();
            $endDate = now()->endOfDay();
        }

        // Key Metrics
        $totalJobs = Job::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalRevenue = Payment::where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        // Commission is dynamic from settings (default 10%)
        $commissionRate = (float) Setting::get('commission_rate', 10) / 100;
        $totalCommissions = Job::where('status', 'completed')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->sum(DB::raw("price * $commissionRate"));

        $activeUsers = User::where('is_active', true)->count();

        $completedJobsCount = Job::where('status', 'completed')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();
        $completionRate = $totalJobs > 0 ? ($completedJobsCount / $totalJobs) * 100 : 0;

        // User growth
        $userGrowth = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Job statistics
        $jobStats = Job::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue Trend
        $revenueTrend = Payment::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(amount) as total')
        )
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top workers
        $topWorkers = User::where('role', 'mfanyakazi')
            ->withCount([
                'assignedJobs as completed_jobs' => function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'completed')->whereBetween('updated_at', [$startDate, $endDate]);
                }
            ])
            ->withSum([
                'assignedJobs as total_earned' => function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'completed')->whereBetween('updated_at', [$startDate, $endDate]);
                }
            ], 'price')
            ->orderBy('completed_jobs', 'desc')
            ->limit(5)
            ->get();

        // Job Categories Distribution
        $categoryDistribution = Category::withCount([
            'jobs' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }
        ])
            ->orderBy('jobs_count', 'desc')
            ->limit(5)
            ->get();

        // Job Status Distribution
        $jobStatuses = Job::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('admin.analytics', compact(
            'totalJobs',
            'totalRevenue',
            'totalCommissions',
            'activeUsers',
            'completionRate',
            'userGrowth',
            'jobStats',
            'revenueTrend',
            'topWorkers',
            'categoryDistribution',
            'jobStatuses',
            'period',
            'startDate',
            'endDate'
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

        return redirect()->route('dashboard')->with(
            'success',
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
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('admin.system-settings', compact('settings'));
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

        // Handle Platform Logo upload
        if ($request->hasFile('platform_logo')) {
            $request->validate([
                'platform_logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            $logoPath = $request->file('platform_logo')->store('platform', 'public');
            Setting::set('platform_logo', $logoPath);
        }

        // Handle other system settings
        $settings = $request->except(['_token', 'apk_file', 'apk_version', 'apk_description', 'platform_logo']);

        foreach ($settings as $key => $value) {
            // Handle checkboxes (toggles)
            if ($value === 'on')
                $value = '1';

            Setting::set($key, $value);
        }

        // Special case for toggles that might be missing from request if unchecked
        $toggles = ['email_notifications', 'sms_notifications', 'push_notifications', 'payments_enabled'];
        foreach ($toggles as $toggle) {
            if (!$request->has($toggle)) {
                Setting::set($toggle, '0');
            } else {
                Setting::set($toggle, '1');
            }
        }

        return back()->with('success', 'System settings updated successfully!');
    }

    /**
     * ADMIN FULL CONTROL - Upload APK file (AJAX)
     */
    public function uploadApk(Request $request)
    {
        // Increase execution time and memory for large file processing
        set_time_limit(3600); // 1 hour
        ini_set('max_execution_time', '3600');
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

            // Check if file is valid
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File upload failed: ' . $file->getErrorMessage()
                ], 400);
            }

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
                'apk_file' => 'required|file|mimes:apk,zip,application/vnd.android.package-archive,application/octet-stream|max:102400', // Max 100MB
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
            $parts[2] = (int) $parts[2] + 1;
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

    /**
     * ADMIN FULL CONTROL - Scan for manually uploaded APK
     */
    public function scanManualApk()
    {
        $apkDir = storage_path('app/public/apk');

        // Ensure directory exists
        if (!file_exists($apkDir)) {
            mkdir($apkDir, 0755, true);
            return back()->with('error', "Directory created at {$apkDir}. Please upload an APK file there and try again.");
        }

        // Scan for .apk files
        $files = glob($apkDir . '/*.apk');

        if (empty($files)) {
            return back()->with('error', "No APK files found in {$apkDir}. Please upload a file via FTP/File Manager first.");
        }

        // Get the most recently modified file
        $latestFile = null;
        $latestTime = 0;

        foreach ($files as $file) {
            $time = filemtime($file);
            if ($time > $latestTime) {
                $latestTime = $time;
                $latestFile = $file;
            }
        }

        if (!$latestFile) {
            return back()->with('error', 'Could not determine the latest file.');
        }

        $fileName = basename($latestFile);
        $filePath = 'apk/' . $fileName;
        $fileSize = filesize($latestFile);

        // Guess version from filename (e.g., tendapoa-1.0.0.apk)
        $version = '1.0.0'; // Default
        if (preg_match('/(\d+\.\d+\.\d+)/', $fileName, $matches)) {
            $version = $matches[1];
        } else {
            // Auto-increment from DB if not found in filename
            $lastVersion = AppVersion::latest()->first();
            if ($lastVersion) {
                $parts = explode('.', $lastVersion->version);
                if (count($parts) === 3) {
                    $parts[2] = (int) $parts[2] + 1;
                    $version = implode('.', $parts);
                }
            }
        }

        // Check if this specific file is already recorded
        $existing = AppVersion::where('file_name', $fileName)->first();
        if ($existing) {
            // Just activate it
            AppVersion::where('is_active', true)->update(['is_active' => false]);
            $existing->update(['is_active' => true]);
            return back()->with('success', "Existing version {$existing->version} activated successfully!");
        }

        // Deactivate previous
        AppVersion::where('is_active', true)->update(['is_active' => false]);

        // Create new record
        AppVersion::create([
            'version' => $version,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'is_active' => true,
            'description' => 'Manually scanned and activated via Admin Panel',
        ]);

        return back()->with('success', "Found and activated APK: {$fileName} (Version: {$version})");
    }

    /**
     * CATEGORY MANAGEMENT - List all categories
     */
    public function categories()
    {
        $categories = Category::withCount('jobs')->orderBy('name')->get();
        return view('admin.categories', compact('categories'));
    }

    /**
     * CATEGORY MANAGEMENT - Store new category
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ], [
            'name.required' => 'Category name is required.',
            'name.unique' => 'This category already exists.',
        ]);

        $slug = \Illuminate\Support\Str::slug($request->name);

        // Ensure unique slug
        $originalSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        Category::create([
            'name' => $request->name,
            'slug' => $slug,
        ]);

        return back()->with('success', "Category '{$request->name}' created successfully!");
    }

    /**
     * CATEGORY MANAGEMENT - Update category
     */
    public function updateCategory(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ], [
            'name.required' => 'Category name is required.',
            'name.unique' => 'This category already exists.',
        ]);

        $slug = \Illuminate\Support\Str::slug($request->name);

        // Ensure unique slug (exclude current)
        $originalSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $category->update([
            'name' => $request->name,
            'slug' => $slug,
        ]);

        return back()->with('success', "Category updated to '{$request->name}' successfully!");
    }

    /**
     * CATEGORY MANAGEMENT - Delete category
     */
    public function deleteCategory(Category $category)
    {
        // Check if category has jobs
        $jobCount = Job::where('category_id', $category->id)->count();

        if ($jobCount > 0) {
            return back()->with('error', "Cannot delete '{$category->name}' - it has {$jobCount} jobs. Please reassign or delete those jobs first.");
        }

        $categoryName = $category->name;
        $category->delete();

        return back()->with('success', "Category '{$categoryName}' deleted successfully!");
    }
    /**
     * Show broadcast form
     */
    public function showBroadcast()
    {
        return view('admin.broadcast');
    }

    /**
     * Send broadcast notification
     */
    public function sendBroadcast(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target' => 'required|in:all,muhitaji,mfanyakazi',
        ]);

        $target = $request->target;
        $users = User::query();

        if ($target !== 'all') {
            $users->where('role', $target);
        }

        // Count users to notify
        $count = $users->count();

        // Chunking for performance if many users
        $users->chunk(100, function ($chunk) use ($request) {
            foreach ($chunk as $user) {
                try {
                    $user->notify(new \App\Notifications\AdminMessageNotification(
                        $request->title,
                        $request->message
                    ));
                } catch (\Exception $e) {
                    \Log::error('Broadcast error for user ' . $user->id . ': ' . $e->getMessage());
                }
            }
        });

        // Also save to SystemNotification table for history
        \App\Models\SystemNotification::create([
            'title' => $request->title,
            'message' => $request->message,
            'target' => $target,
            'sent_by' => Auth::id(),
        ]);

        return back()->with('success', "Taarifa imetumwa kwa watumiaji {$count} (" . ($target == 'all' ? 'Wote' : ucfirst($target)) . ').');
    }
}

