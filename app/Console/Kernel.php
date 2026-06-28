<?php

namespace App\Console;

use App\Services\NotificationService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('attendance:auto-close')->dailyAt('23:00');

        $schedule->call(function () {
            app(NotificationService::class)->notifyWeeklyFinancialSummary();
        })->weeklyOn(1, '08:00'); // Every Monday at 8am
        
        $schedule->call(function () {
            app(NotificationService::class)->notifyMonthlyFinancialSummary();
        })->monthlyOn(1, '08:00'); // 1st of every month at 8am
        
        $schedule->call(function () {
            // Withdrawal ending soon — checks daily
            $expiring = \App\Models\Treatment::with('flock')
                ->whereNotNull('withdrawal_end_date')
                ->whereBetween('withdrawal_end_date', [now(), now()->addDays(3)])
                ->get();
        
            $service = app(NotificationService::class);
            foreach ($expiring as $treatment) {
                $service->notifyWithdrawalEndingSoon(
                    $treatment->flock_id,
                    $treatment->flock->flock_number ?? 'Unknown',
                    $treatment->product_name,
                    $treatment->withdrawal_end_date->format('d M Y')
                );
            }
        })->dailyAt('07:00');
    }

    
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

}
