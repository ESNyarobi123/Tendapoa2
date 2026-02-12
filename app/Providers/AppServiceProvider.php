<?php

namespace App\Providers;

use App\Console\Commands\TranslateLegacyJobsCommand;
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
                $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
                view()->share('settings', $settings);
            }
        } catch (\Exception $e) {
            // Table might not exist during migration
        }
    }
}
