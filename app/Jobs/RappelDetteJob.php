<?php

namespace App\Jobs;

use App\Models\Dette;
use App\Notifications\RappelDetteNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RappelDetteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Récupérer toutes les dettes non soldées
        // $dettes = Dette::whereColumn('montant', '>', 'montant_paiement')->get();
        $dettes = Dette::whereRaw('COALESCE(montant, 0) > COALESCE(montant_paiement, 0)')->get();
        
        // Parcourir les dettes et notifier les clients par email
        foreach ($dettes as $dette) {
            $client = $dette->client;
            $user = $client->user;  // User associé via user_id

            // Vérifier si l'utilisateur a un email valide
            if ($user && $user->email) {
                $montantRestant = $dette->montant - $dette->montant_paiement;
                $user->notify(new RappelDetteNotification($client, $dette->montant, $montantRestant));
            }
        }
    }
}
