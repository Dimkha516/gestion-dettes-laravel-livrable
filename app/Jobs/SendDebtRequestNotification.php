<?php

namespace App\Jobs;

use App\Models\DemandeDeDette;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\DebtRequestNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDebtRequestNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $demandeDeDette;

    public function __construct(DemandeDeDette $demandeDeDette)
    {
        $this->demandeDeDette = $demandeDeDette;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        
        $boutiquiers = User::where('role', 'boutiquier')->get();
        foreach ($boutiquiers as $boutiquier) {
            // Notification::send($boutiquier, new DebtRequestNotification($this->demandeDeDette));
            $boutiquier->notify(new DebtRequestNotification($this->demandeDeDette));

        }
    }
}
