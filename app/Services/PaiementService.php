<?php
namespace App\Services;
use App\Models\PaiementDette;
use App\Repositories\PaiementRepository;

class PaiementService
{
    protected $paiementRepo;

    public function __construct(PaiementRepository $paiementRepo)
    {
        $this->paiementRepo = $paiementRepo;
    }

    public function getAllPayments(){
        $payments = PaiementDette::all();
        if ($payments->isEmpty()) {
            return ['success' => false, 'message' => 'Aucun paiement existe dans la base de données', 'status' => 404];
        }

        return ['success' => true, 'message' => 'Liste des paiements', 'data' => $payments, 'status' => 200];


    }

    
    public function addPayment($detteId, $amount)
    {
        $dette = $this->paiementRepo->getDette($detteId);

        if (!$dette) {
            throw new \Exception("Dette non trouvée");
        }

        if ($amount <= 0) {
            throw new \Exception("Le montant doit être positif");
        }

        if ($dette->montant_paiement + $amount > $dette->montant) {
            throw new \Exception("Le montant payé ne peut pas dépasser le montant de la dette");
        }

        if ($dette->montant_paiement == $dette->montant) {
            throw new \Exception("La dette est déjà complètement réglée");
        }

        // Ajouter le paiement
        $this->paiementRepo->createPayment($detteId, $amount);

        // Mettre à jour la dette
        $this->paiementRepo->updateDettePayment($detteId, $amount);
    }
}