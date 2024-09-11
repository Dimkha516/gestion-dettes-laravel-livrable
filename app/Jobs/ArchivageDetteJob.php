<?php

namespace App\Jobs;

use App\Models\Dette;
use DB;
use Kreait\Firebase\Factory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ArchivageDetteJob implements ShouldQueue
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
        //----------------------- FAILED CONNEXION FIREBASE-----------

        // // Récupérer toutes les dettes soldées (montant = paiement)
        // $dettesSoldees = Dette::whereColumn('montant', 'montant_paiement')->get();

        // if ($dettesSoldees->isEmpty()) {
        //     return;
        // }

        // // Initialiser Firebase avec le Factory (plus besoin de ServiceAccount)

        // $firebase = (new Factory)
        //     ->withServiceAccount(config('firebase.credentials'))
        //     ->withDatabaseUri(config('firebase.database_url')) // Utilisez l'URL de la base de données
        //     ->createDatabase();

        // $database = $firebase->getReference('archived_dettes');

        // // Archiver chaque dette dans Firebase
        // foreach ($dettesSoldees as $dette) {
        //     $database->push([
        //         'client_id' => $dette->client_id,
        //         'montant' => $dette->montant,
        //         'montant_paiement' => $dette->montant_paiement,
        //         'date' => $dette->created_at,
        //     ]);

        //     // Vous pouvez aussi supprimer les dettes de la base locale si nécessaire
        //     $dette->delete();
        // }

        //----------------------- CONNEXION MONGO_DB-----------
        // Récupérer les dettes soldées (montant == paiement) à partir de MySQL
        $dettesSoldees = DB::connection('mysql')->table('dettes')
            ->whereColumn('montant', '=', 'montant_paiement')
            ->get();

        // Pour chaque dette, l'archiver dans MongoDB
        foreach ($dettesSoldees as $dette) {
            DB::connection('mongodb')->collection('archived_debts')->insert([
                'dette_id' => $dette->id,
                'montant' => $dette->montant,
                'date' => $dette->created_at,
                'archived_at' => now(),
            ]);
        }
    }
}
