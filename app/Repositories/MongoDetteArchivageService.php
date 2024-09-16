<?php

namespace App\Repositories;
use DB;

class MongoDetteArchivageService implements DetteArchivageInterface
{
    protected $collection;

    public function __construct()
    {
        $this->collection = DB::connection('mongodb')->collection('archived_debts');
    }

    public function getAllArchivedDebts($filters = [])
    {
        // Construire la requête MongoDB en fonction des filtres fournis
        $query = [];

        // Filtrer par client_id
        if (!empty($filters['client_id'])) {
            $query['client_id'] = (int) $filters['client_id'];
        }

        // Filtrer par date
        if (!empty($filters['date'])) {
            $query['date'] = $filters['date'];
        }

        return $this->collection->find($query)->toArray();
    }
}
