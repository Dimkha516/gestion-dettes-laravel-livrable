<?php
namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Illuminate\Support\Facades\DB;

class FirebaseArchivageService implements ArchivageServiceInterface
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('services.firebase.credentials'))
            ->withDatabaseUri(config('services.firebase.database_url'));  // Utilise Firebase Realtime Database URL
        $this->database = $factory->createDatabase();  // Connexion à la base de données Realtime
    }

    public function archiverDette($dette)
    {
        // Ajouter la dette dans la base de données Firebase
        $this->database->getReference('archived_debts/' . $dette->id)
            ->set([
                'dette_id' => $dette->id,
                'client_id' => $dette->client_id,
                'montant' => $dette->montant,
                'montant_paiement' => $dette->montant_paiement,
                'date' => $dette->created_at,
                'dateEcheance' => $dette->dateEcheance,
                'archived_at' => now(),
            ]);
    }

    public function supprimerDette($detteId)
    {
        // Supprimer la dette dans MySQL après archivage
        DB::connection('mysql')->table('dettes')->where('id', $detteId)->delete();
    }
}
