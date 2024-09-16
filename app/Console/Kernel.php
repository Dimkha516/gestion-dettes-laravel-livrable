<?php

namespace App\Console;
use App\Jobs\ArchivageDetteJob;
use App\Jobs\NotifyOverdueDebtsJob;
use App\Jobs\RappelDetteJob;
use App\Jobs\RappelDetteSmsJob;
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


        //-------------------------------------------------RAPPEL EMAIL DETTE NON RÉGLÉE::
        // Planifier le rappel des dettes par email tous les vendredis à 14h
        // $schedule->job(new RappelDetteJob())->fridays()->at('14:00');


        //-------------------------------------------------RAPPEL SMS DETTE NON RÉGLÉE:
        // Planifier le rappel des dettes par sms tous les vendredis à 14h
        // $schedule->job(new RappelDetteSmsJob())->weeklyOn(5, '14:00'); // Chaque vendredi à 14h
        // $schedule->job(new RappelDetteSmsJob())->everyMinute();



        //-------------------------------------------------ARCHIVAGE DETTES SOLDÉES:
        // Exécute le job d'archivage des dettes soldées tous les jours à minuit
        // $schedule->job(new ArchivageDetteJob())->dailyAt('00:00');

        
        $schedule->job(new ArchivageDetteJob())->everyMinute();


        //------------------------------------DETTE ECHEANCE DÉPASSÉE

        // Exécuter le job tous les jours à 9h00
        // $schedule->job(new NotifyOverdueDebtsJob())->dailyAt('09:00');
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
