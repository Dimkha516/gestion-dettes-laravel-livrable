<?php

namespace App\Console;

use App\Jobs\ArchivageDetteJob;
use App\Jobs\RappelDetteJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // Planifier le rappel tous les vendredis à 14h
        $schedule->job(new RappelDetteJob())->fridays()->at('14:00');
        // Exécute le job d'archivage des dettes soldées tous les jours à minuit
        $schedule->job(new ArchivageDetteJob())->dailyAt('00:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
