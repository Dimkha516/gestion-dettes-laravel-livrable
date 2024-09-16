<?php
namespace App\Services;
use DB;

class MongoArchivageService implements ArchivageServiceInterface{
    public function archiverDette($dette)
    {
        DB::connection('mongodb')->collection('archived_debts')->insert([
            'dette_id' => $dette->id,
            "client_id" => $dette->client_id,
            'montant' => $dette->montant,
            'montant_paiement' => $dette->montant_paiement,
            'date' => $dette->created_at,
            'dateEcheance' => $dette->dateEcheance,
            'archived_at' => now(),
        ]);
    }

    public function supprimerDette($detteId)
    {
        DB::connection('mysql')->table('dettes')->where('id', $detteId)->delete();
    }
}