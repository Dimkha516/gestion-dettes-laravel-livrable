<?php
namespace App\Repositories;

use Kreait\Firebase\Factory;

class FirebaseDetteArchivageService implements DetteArchivageInterface
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory)        
            ->withServiceAccount(config('services.firebase.credentials'))
            ->withDatabaseUri(config('services.firebase.database_url'));
        $this->database = $factory->createDatabase();
    }

    public function getAllArchivedDebts($filters = [])
    {
        // Firebase Realtime Database doesn't have complex querying, so we need to filter manually.
        $debts = $this->database->getReference('archived_debts')->getValue();
        $filteredDebts = [];

        foreach ($debts as $debt) {
            // Apply client_id filter
            if (!empty($filters['client_id']) && $debt['client_id'] != $filters['client_id']) {
                continue;
            }

            // Apply date filter
            if (!empty($filters['date']) && $debt['date'] != $filters['date']) {
                continue;
            }

            $filteredDebts[] = $debt;
        }

        return $filteredDebts;
    }
}
