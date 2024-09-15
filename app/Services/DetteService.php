<?php
namespace App\Services;
use App\Models\Dette;
use App\Repositories\ClientRepository;
use App\Repositories\DetteRepository;
use Request;

class DetteService
{
    protected $detteRepo;
    protected $clientRepo;

    public function __construct(DetteRepository $detteRepo, ClientRepository $clientRepo)
    {
        $this->detteRepo = $detteRepo;
        $this->clientRepo = $clientRepo;
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


    /* Récupérer toutes les dettes archivées avec les informations sur les clients,
     * et appliquer les filtres si disponibles.
     */

    public function getAllArchivedDebts($filters = [])
    {
        // Récupérer les dettes archivées avec les filtres depuis MongoDB
        $dettes = $this->detteRepo->getAllArchivedDebts($filters);

        // Initialiser une liste vide pour stocker les dettes et les clients associés
        $dettesWithClients = [];

        foreach ($dettes as $dette) {
            // Récupérer les informations du client depuis la table SQL (ou une autre source)
            $client = $this->clientRepo->findClientById($dette['client_id']);

            // Vérifier si un client est trouvé (évitons les données manquantes)
            if ($client) {
                // Ajouter la dette et le client dans la liste
                $dettesWithClients[] = [
                    'dette' => $dette,
                    'client' => $client
                ];
            } else {
                // En cas de client manquant, on peut ajouter la dette seule (optionnel)
                $dettesWithClients[] = [
                    'dette' => $dette,
                    'client' => null  // Ou un message d'erreur si vous voulez
                ];
            }

        }
        return $dettesWithClients;

    }

    // Méthode pour récupérer les dettes archivées par client_id
    public function getDettesByClientId($clientId)
    {
        return $this->detteRepo->getDettesByClientId($clientId);
    }

    /**
     * Archiver une nouvelle dette.
     */
    public function archiveDebt($data)
    {
        return $this->detteRepo->archiveDebt($data);
    }

    /**
     * Obtenir une dette archivée par ID.
     */
    public function findArchivedDebtById($id)
    {
        return $this->detteRepo->findArchivedDebtById($id);
    }



    //-------------------- PARTIE RESTAURATION DETTES ARCHIVÉES:
    // Restaurer une dette par ID
    public function restoreDetteById($idDette)
    {
        // Récupérer la dette dans MongoDB
        $dette = $this->detteRepo->findDetteById($idDette);

        if ($dette) {
            // Restaurer dans MySQL
            $this->detteRepo->restoreDetteToMySQL($dette);

            // Supprimer de MongoDB
            $this->detteRepo->deleteDetteFromMongo($idDette);

            return ['success' => true, 'message' => "La dette avec l'ID $idDette a été restaurée et supprimée de MongoDB."];
        }

        return ['success' => false, 'message' => "La dette avec l'ID $idDette n'a pas été trouvée."];
    }

    // Restaurer les dettes d'un client
    public function restoreDettesByClientId($clientId)
    {
        // Récupérer les dettes dans MongoDB
        $dettes = $this->detteRepo->findDettesByClientId($clientId);

        if ($dettes->isNotEmpty()) {
            foreach ($dettes as $dette) {
                // Restaurer chaque dette dans MySQL
                $this->detteRepo->restoreDetteToMySQL($dette);

                // Supprimer chaque dette de MongoDB
                $this->detteRepo->deleteDetteFromMongo($dette['dette_id']);
            }

            return ['success' => true, 'message' => "Les dettes du client $clientId ont été restaurées et supprimées de MongoDB."];
        }

        return ['success' => false, 'message' => "Aucune dette trouvée pour le client $clientId."];
    }

    public function restoreDettesByDate($date){
        // Récupérer les dettes dans MongoDB
        $dettes = $this->detteRepo->findDettesByDate($date);

        if ($dettes->isNotEmpty()) {
            foreach ($dettes as $dette) {
                // Restaurer chaque dette dans MySQL
                $this->detteRepo->restoreDetteToMySQL($dette);

                // Supprimer chaque dette de MongoDB
                $this->detteRepo->deleteDetteFromMongo($dette['dette_id']);
            }

            return ['success' => true, 'message' => "Les dettes du $date ont été restaurées et supprimées de MongoDB."];
        }

        return ['success' => false, 'message' => "Aucune dette trouvée pour la date du $date."];

    }




    // Restaurer les dettes par date
    // public function restoreDettesByDate($date)
    // {
    //     $dettes = $this->detteRepo->getDettesByDate($date);
    //     foreach ($dettes as $dette) {
    //         // Restaurer la dette dans la base de données locale
    //         Dette::create([
    //             'client_id' => $dette['client_id'],
    //             'montant' => $dette['montant'],
    //             'montant_paiement' => $dette['montant_paiement'],
    //             'date' => $dette['date'],
    //             // autres attributs
    //         ]);
    //     }
    //     // Supprimer les dettes restaurées de MongoDB
    //     $this->detteRepo->deleteDettesByDate($date);
    // }


    // // Restaurer une dette par id_dette
    // public function restoreDetteById($idDette)
    // {
    //     return $this->detteRepo->getDetteById($idDette);

    // }


    // // Restaurer les dettes par client_id
    // public function restoreDettesByClientId($clientId)
    // {
    //     $dettes = $this->detteRepo->getDettesByClientId($clientId);
    //     foreach ($dettes as $dette) {
    //         // Restaurer la dette dans la base de données locale
    //         Dette::create([
    //             'client_id' => $dette['client_id'],
    //             'montant' => $dette['montant'],
    //             'montant_paiement' => $dette['montant_paiement'],
    //             'date' => $dette['date'],
    //             // autres attributs
    //         ]);
    //     }
    //     // Supprimer les dettes restaurées de MongoDB
    //     $this->detteRepo->deleteDettesByClientId($clientId);
    // }



}