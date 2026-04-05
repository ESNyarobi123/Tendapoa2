<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Payment;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $u = Auth::user();

        if ($u && $u->role === 'admin') {
            // Usitumie view hapa — AdminController::dashboard ndiyo ina $stats, $recentUsers, n.k.
            return redirect()->route('admin.dashboard');
        }

        if ($u && $u->role === 'muhitaji') {
            $posted = Job::where('user_id', $u->id)->count();
            $completed = Job::where('user_id', $u->id)->where('status', 'completed')->count();
            $totalPaid = Payment::whereHas('job', fn ($q) => $q->where('user_id', $u->id))
                ->where('status', 'COMPLETED')
                ->sum('amount');

            // Get payment history for muhitaji
            $paymentHistory = Payment::whereHas('job', fn ($q) => $q->where('user_id', $u->id))
                ->with('job')
                ->latest()
                ->limit(10)
                ->get();

            // Get all jobs with their status and amounts
            $allJobs = Job::where('user_id', $u->id)
                ->with('acceptedWorker', 'category', 'payment')
                ->latest()
                ->limit(10)
                ->get();

            // Get wallet balance for muhitaji (salio linaloweza kutumia = balance − held)
            $wallet = $u->ensureWallet();
            $available = $wallet->available_balance;

            $attentionJobs = Job::where('user_id', $u->id)
                ->whereIn('status', [Job::S_AWAITING_PAYMENT, Job::S_SUBMITTED])
                ->with('acceptedWorker', 'category')
                ->latest()
                ->limit(5)
                ->get();

            $notifications = $u->unreadNotifications()->latest()->limit(10)->get();

            $pendingAppsCount = JobApplication::query()
                ->whereHas('job', fn ($q) => $q->where('user_id', $u->id))
                ->whereIn('status', [
                    JobApplication::STATUS_APPLIED,
                    JobApplication::STATUS_SHORTLISTED,
                    JobApplication::STATUS_ACCEPTED_COUNTER,
                ])
                ->count();

            return view('muhitaji.dashboard', compact(
                'posted',
                'completed',
                'totalPaid',
                'paymentHistory',
                'allJobs',
                'available',
                'notifications',
                'pendingAppsCount',
                'attentionJobs',
            ));
        }

        // default: mfanyakazi
        $done = Job::where('accepted_worker_id', $u->id)->where('status', 'completed')->count();

        // Get earnings from wallet transactions instead of just completed jobs
        $earnTotal = WalletTransaction::where('user_id', $u->id)
            ->where('type', 'EARN')
            ->where('amount', '>', 0)
            ->sum('amount');

        $withdrawn = Withdrawal::where('user_id', $u->id)->whereIn('status', ['PAID', 'PROCESSING'])->sum('amount');

        // Get current wallet balance (inapatikana = balance − held)
        $wallet = $u->ensureWallet();
        $available = $wallet->available_balance;

        $attentionJobs = Job::where('accepted_worker_id', $u->id)
            ->whereIn('status', [Job::S_FUNDED, Job::S_IN_PROGRESS])
            ->with('muhitaji', 'category')
            ->latest()
            ->limit(5)
            ->get();

        // Get current jobs for dashboard (mfumo mpya + legacy)
        $currentJobs = Job::with('muhitaji', 'category')
            ->where('accepted_worker_id', $u->id)
            ->whereIn('status', [
                Job::S_FUNDED,
                Job::S_IN_PROGRESS,
                Job::S_SUBMITTED,
                'assigned',
                'in_progress',
                'ready_for_confirmation',
            ])
            ->latest()
            ->limit(5)
            ->get();

        // Calculate earnings this month
        $thisMonthEarnings = Job::where('accepted_worker_id', $u->id)
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->startOfMonth())
            ->sum('price');

        // Calculate average job completion time (in days)
        $avgCompletionTime = Job::where('accepted_worker_id', $u->id)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereNotNull('created_at')
            ->selectRaw('AVG(DATEDIFF(completed_at, created_at)) as avg_days')
            ->value('avg_days') ?? 0;

        // Get earnings history (wallet transactions)
        $earningsHistory = WalletTransaction::where('user_id', $u->id)
            ->where('type', 'EARN')
            ->where('amount', '>', 0)
            ->latest()
            ->limit(10)
            ->get();

        // Get withdrawals history
        $withdrawalsHistory = Withdrawal::where('user_id', $u->id)
            ->latest()
            ->limit(10)
            ->get();

        // Get completed jobs for earnings breakdown
        $completedJobs = Job::where('accepted_worker_id', $u->id)
            ->where('status', 'completed')
            ->with('muhitaji', 'category')
            ->latest()
            ->limit(10)
            ->get();

        $notifications = $u->unreadNotifications()->latest()->limit(10)->get();

        return view('mfanyakazi.dashboard', compact(
            'done',
            'earnTotal',
            'withdrawn',
            'available',
            'currentJobs',
            'thisMonthEarnings',
            'avgCompletionTime',
            'earningsHistory',
            'withdrawalsHistory',
            'completedJobs',
            'notifications',
            'attentionJobs',
        ));
    }
}
