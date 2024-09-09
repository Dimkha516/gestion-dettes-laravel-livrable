<?php
namespace App\Services;
use App\Models\Dette;
use App\Repositories\DetteRepository;
use Request;

class DetteService
{
    protected $detteRepo;

    public function __construct(DetteRepository $detteRepo)
    {
        $this->detteRepo = $detteRepo;
    }

    public function getAllDettes()
    {
        $dettes = Dette::all();

        if ($dettes->isEmpty()) {
            return ['success' => false, 'message' => 'Aucun dette existe dans la base de données', 'status' => 404];
        }

        return ['success' => true, 'message' => 'Liste des dettes', 'data' => $dettes, 'status' => 200];



    }

    public function createDette(array $data)
    {
        return $this->detteRepo->createDette($data);
    }

    // Méthode pour obtenir les dettes filtrées par solde
    public function filterDettesBySolde($solde)
    {
        return $this->detteRepo->getDettesBySolde($solde);
    }

    // Méthode pour obtenir les articles d'une dette
    public function getArticlesByDetteId($detteId)
    {
        return $this->detteRepo->getArticlesByDetteId($detteId);
    }

    public function getPaiementsByDette($detteId)
    {
        $dette = Dette::with('paiements')->find($detteId);

        if (!$dette) {
            return null;
        }

        return $dette->paiements;
    }

    


}