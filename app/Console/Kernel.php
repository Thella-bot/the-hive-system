<?php
declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('cohorts:update-status')->daily();
        $schedule->command('academic-year:create')->yearlyOn(12, 1, '00:00');
        $schedule->command('hive:backup --type=daily --compress')->dailyAt(2, 0);
        $schedule->command('hive:backup --type=weekly --compress')->weeklyOn(0, 3, 0);
        $schedule->command('hive:backup --type=monthly --compress')->monthlyOn(1, 4, 0);
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}