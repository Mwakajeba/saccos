<?php

namespace App\Providers;

use App\Jobs\CalculateDailyInterestJob;
use App\Jobs\CollectMatureInterestJob;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
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
        // Configure rate limiters
        $this->configureRateLimiters();

        // Run mature interest collection once per day on first user login
        Event::listen(Login::class, function () {
            Log::info('Login event triggered - checking scheduled jobs');
            
            // Run mature interest collection
            try {
                $cacheKey = 'mature_interest_job_ran_' . Carbon::today()->toDateString();

                // Only run once per day; Cache::add sets the key if it does not exist
                $added = Cache::add($cacheKey, true, Carbon::now()->endOfDay());
                if (!$added) {
                    Log::info('Mature interest job already ran today, skipping');
                } else {
                    Log::info('Running CollectMatureInterestJob synchronously from login event (once per day)');
                    dispatch_sync(new CollectMatureInterestJob());
                }
            } catch (\Throwable $e) {
                Log::error('Failed dispatching CollectMatureInterestJob on login: ' . $e->getMessage());
            }

            // Run daily accrued interest calculation
            try {
                $cacheKey = 'daily_interest_job_ran_' . Carbon::today()->toDateString();

                // Only run once per day
                $added = Cache::add($cacheKey, true, Carbon::now()->endOfDay());
                if (!$added) {
                    Log::info('Daily interest job already ran today, skipping');
                } else {
                    Log::info('Running CalculateDailyInterestJob synchronously from login event (once per day)');
                    dispatch_sync(new CalculateDailyInterestJob());
                }
            } catch (\Throwable $e) {
                Log::error('Failed dispatching CalculateDailyInterestJob on login: ' . $e->getMessage());
            }
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiters(): void
    {
        // Rate limiter for password reset requests
        RateLimiter::for('password_reset', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Rate limiter for OTP verification
        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Rate limiter for login attempts
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Rate limiter for API requests
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
