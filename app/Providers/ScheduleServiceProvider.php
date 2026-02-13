<?php

namespace App\Providers;

//use App\Jobs\CollectMatureInterestJob;
use App\Jobs\RepaymentReminderJob;
use App\Jobs\CheckSubscriptionExpiryJob;
use App\Jobs\CalculateContributionInterestJob;
use App\Jobs\AccruePenaltyJob;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Schedule mature interest collection to run daily at midnight
           // $schedule->job(new CollectMatureInterestJob())
                // ->dailyAt('08:00')
                // ->everyMinute()
              //  ->everySecond()
              //  ->withoutOverlapping()
             //   ->onOneServer()
              //  ->appendOutputTo(storage_path('logs/mature-interest-collection.log'));

            // Schedule repayment reminders to run daily at 08:00 AM
            $schedule->job(new RepaymentReminderJob())
                ->dailyAt('08:00')
                // ->everyMinute()
                ->withoutOverlapping()
                ->onOneServer()
                ->appendOutputTo(storage_path('logs/repayment-reminder.log'));

            // Schedule subscription expiry check to run every minute
            $schedule->job(new CheckSubscriptionExpiryJob())
                ->dailyAt('00:00')
                ->withoutOverlapping()
                ->onOneServer()
                ->appendOutputTo(storage_path('logs/subscription-expiry-check.log'));

            // Schedule contribution interest calculation to run daily at 9:00 AM
            $schedule->job(new CalculateContributionInterestJob())
                ->dailyAt('09:00')
                ->withoutOverlapping()
                ->onOneServer()
                ->appendOutputTo(storage_path('logs/contribution-interest-calculation.log'));

            // Schedule penalty accrual to run daily at 06:00 AM
            // This calculates penalties for overdue loan schedules, respecting grace periods,
            // posts to GL transactions, creates journal entries, and updates penalty_amount
            $schedule->job(new AccruePenaltyJob())
                ->dailyAt('06:00')
                ->withoutOverlapping()
                ->onOneServer()
                ->appendOutputTo(storage_path('logs/penalty-accrual.log'));

             // Scheduled backups from System Configuration (backup_enabled, backup_frequency).
            // Command runs every hour and decides internally whether to run based on frequency (no app restart needed).
            $schedule->command('backup:run-scheduled')
                ->hourly()
                ->withoutOverlapping(55)
                ->onOneServer()
                ->appendOutputTo(storage_path('logs/scheduled-backup.log'));
        });
    }
}
