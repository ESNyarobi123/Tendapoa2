<?php

namespace App\Providers;

use App\Console\Commands\TranslateLegacyJobsCommand;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Setting;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->commands([TranslateLegacyJobsCommand::class]);
        // Share system settings with all views
        try {
            if (\Schema::hasTable('settings')) {
                $settings = Setting::pluck('value', 'key')->toArray();
                view()->share('settings', $settings);
            }
        } catch (\Exception $e) {
            // Table might not exist during migration
        }

        View::composer('components.user-sidebar', function ($view) {
            $user = auth()->user();
            $pendingAppsCount = 0;
            if ($user && $user->role === 'muhitaji') {
                try {
                    $pendingAppsCount = JobApplication::query()
                        ->whereHas('job', fn ($q) => $q->where('user_id', $user->id))
                        ->whereIn('status', [
                            JobApplication::STATUS_APPLIED,
                            JobApplication::STATUS_SHORTLISTED,
                            JobApplication::STATUS_ACCEPTED_COUNTER,
                        ])
                        ->count();
                } catch (\Throwable $e) {
                    $pendingAppsCount = 0;
                }
            }
            $view->with('pendingAppsCount', $pendingAppsCount);
        });

        View::composer('layouts.admin', function ($view) {
            $badges = [
                'withdrawals_pending' => 0,
                'disputed_jobs' => 0,
            ];
            $user = auth()->user();
            if ($user && $user->role === 'admin') {
                try {
                    if (Schema::hasTable('withdrawals')) {
                        $badges['withdrawals_pending'] = Withdrawal::where('status', 'PROCESSING')->count();
                    }
                    if (Schema::hasTable('work_orders')) {
                        $badges['disputed_jobs'] = Job::where('status', Job::S_DISPUTED)->count();
                    }
                } catch (\Throwable $e) {
                    // DB not ready
                }
            }
            $view->with('adminNavBadges', $badges);
        });
    }
}
