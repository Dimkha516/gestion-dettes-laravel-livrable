<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Http\Requests\DetteRequest;
use App\Services\DetteService;
use Illuminate\Http\JsonResponse;
use Auth;

class DetteController extends Controller
{
    protected $detteService;

    public function __construct(DetteService $detteService)
    {
        $this->detteService = $detteService;
    }

    // LISTER TOUTES LES DETTES:
    public function index(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Connectez vous d'abord."
            ], 403);
        }
        if (!in_array($user->role, ['admin', 'boutiquier'])) {
            return response()->json([
                'message' => 'Autorisation rejettée. Seuls les admins peuvent lister les utilisateurs.'
            ], 403);
        }


        $dettes = $this->detteService->getAllDettes();

        return response()->json([
            'success' => $dettes['success'],
            'message' => $dettes['message'],
            'data' => $dettes['data'] ?? null,
        ]);
    }

    public function store(DetteRequest $request)
    {

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Connectez vous d'abord."
            ], 403);
        }
        if (!in_array($user->role, ['admin', 'boutiquier'])) {
            return response()->json([
                'message' => 'Autorisation rejettée. Seuls les admins ou boutiquiers peuvent ajouter une dette.'
            ], 403);
        }

        $validatedData = $request->validated();

        try {
            $dette = $this->detteService->createDette($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Dette créée avec succès',
                'data' => $dette
            ], 201);
        }
        // 
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la dette',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    // Endpoint pour filtrer les dettes par état soldé ou non soldé
    public function filterDettes(Request $request)
    {

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Connectez vous d'abord."
            ], 403);
        }
        if (!in_array($user->role, ['admin', 'boutiquier'])) {
            return response()->json([
                'message' => 'Autorisation rejettée. Seuls les admins peuvent effectuer cette action.'
            ], 403);
        }

        $solde = $request->query('solde');

        if ($solde !== 'oui' && $solde !== 'non') {
            return response()->json([
                'success' => false,
                'message' => 'Le paramètre solde doit être "oui" ou "non".'
            ], 400);
        }

        $dettes = $this->detteService->filterDettesBySolde($solde);

        return response()->json([
            'success' => true,
            'data' => $dettes
        ], 200);
    }

    // Endpoint pour obtenir les articles d'une dette
    public function getArticles($id): JsonResponse
    {

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Connectez vous d'abord."
            ], 403);
        }
        if (!in_array($user->role, ['admin', 'boutiquier'])) {
            return response()->json([
                'message' => 'Autorisation rejettée. Seuls les admins peuvent effectuer cette action .'
            ], 403);
        }


        try {
            $articles = $this->detteService->getArticlesByDetteId($id);

            if ($articles->isNotEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => $articles
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Dette non trouvée ou aucun article associé.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des articles de la dette',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPaiementsByDette($detteId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Connectez vous d'abord."
            ], 403);
        }
        if (!in_array($user->role, ['admin', 'boutiquier'])) {
            return response()->json([
                'message' => 'Autorisation rejettée. Seuls les admins peuvent effectuer cette action.'
            ], 403);
        }

        $paiements = $this->detteService->getPaiementsByDette($detteId);

        if (!$paiements) {
            return response()->json([
                'success' => false,
                'message' => 'Dette non trouvée ou aucun paiement associé.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $paiements
        ]);
    }
    /**
     * Liste des dettes archivées avec les informations des clients.
     * Permet de filtrer par date ou client_id.
     */
    public function archivedDebts(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Connectez vous d'abord."
            ], 403);
        }
        if (!in_array($user->role, ['admin', 'boutiquier'])) {
            return response()->json([
                'message' => 'Autorisation rejettée. Seuls les admins peuvent lister les utilisateurs.'
            ], 403);
        }


        try {

            // Récupérer les filtres de la requête
            $filters = [
                'date' => $request->query('date'),
                'client_id' => $request->query('client_id'),
            ];

            // Appeler le service avec les filtres
            $dettesWithClients = $this->detteService->getAllArchivedDebts($filters);


            return response()->json([
                'success' => true,
                'data' => $dettesWithClients
            ], 200);
        }
        // 
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des dettes archivées',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // Endpoint pour récupérer les dettes archivées d'un client spécifique
    public function getDettesByClientId($clientId)
    {

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Connectez vous d'abord."
            ], 403);
        }
        if (!in_array($user->role, ['admin', 'boutiquier'])) {
            return response()->json([
                'message' => 'Autorisation rejettée. Seuls les admins peuvent lister les utilisateurs.'
            ], 403);
        }

        $dettes = $this->detteService->getDettesByClientId($clientId);

        if (empty($dettes)) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune dette trouvée pour ce client.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $dettes
        ]);
    }


    //----------------- PARTIE RESTAURATION DETTES ARCHIVÉES:


    // Restaurer une dette par ID
    public function restoreDetteById($id)
    {
        $result = $this->detteService->restoreDetteById($id);

        return response()->json($result);
    }

    // Restaurer les dettes d'un client par client_id
    public function restoreDettesByClientId($clientId)
    {
        $result = $this->detteService->restoreDettesByClientId($clientId);

        return response()->json($result);
    }

    public function restoreByDate($date){
        $result = $this->detteService->restoreDettesByDate($date);

        return response()->json($result);
    }






    // public function restoreDettesByDate(Request $request)
    // {
    //     $user = Auth::user();
    //     if (!$user) {
    //         return response()->json([
    //             'message' => "Connectez vous d'abord."
    //         ], 403);
    //     }
    //     if (!in_array($user->role, ['admin', 'boutiquier'])) {
    //         return response()->json([
    //             'message' => 'Autorisation rejettée. Seuls les admins peuvent lister les utilisateurs.'
    //         ], 403);
    //     }

    //     $date = $request->input('date');
    //     $this->detteService->restoreDettesByDate($date);

    //     return response()->json([
    //         'success' => true,
    //         'message' => "Les dettes de la date $date ont été restaurées avec succès."
    //     ]);
    // }


    // // Endpoint pour restaurer une dette par id_dette
    // public function restoreDetteById($idDette)
    // {
    //     $user = Auth::user();
    //     if (!$user) {
    //         return response()->json([
    //             'message' => "Connectez vous d'abord."
    //         ], 403);
    //     }
    //     if (!in_array($user->role, ['admin', 'boutiquier'])) {
    //         return response()->json([
    //             'message' => 'Autorisation rejettée. Seuls les admins peuvent lister les utilisateurs.'
    //         ], 403);
    //     }

    //     try {
    //         // Connexion à MongoDB pour récupérer la dette
    //         $detteArchivee = DB::connection('mongodb')->collection('archived_debts')
    //             ->where('dette_id', (int) $idDette)
    //             ->first();

    //         if ($detteArchivee) {

    //             // Vérifiez les types de données et formatez les dates si nécessaire
    //             $clientId = (int) $detteArchivee['client_id'];
    //             $montant = (float) $detteArchivee['montant'];
    //             $montantPaiement = (float) $detteArchivee['montant_paiement'];
    //             $date = \Carbon\Carbon::parse($detteArchivee['date'])->toDateTimeString();


    //             // Restauration de la dette dans la base MySQL
    //             DB::connection('mysql')->table('dettes')->insert([
    //                 'client_id' => $clientId,
    //                 'montant' => $montant,
    //                 'montant_paiement' => $montantPaiement,
    //                 // 'date' => $date,
    //             ]);

    //             // Suppression de la dette de MongoDB après restauration
    //             DB::connection('mongodb')->collection('archived_debts')->where('dette_id', (int) $idDette)->delete();

    //             return response()->json([
    //                 'success' => true,
    //                 'message' => "La dette avec l'ID $idDette a été restaurée et supprimée de MongoDB avec succès."
    //             ]);
    //         } else {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => "La dette avec l'ID $idDette n'a pas été trouvée dans MongoDB."
    //             ]);
    //         }

    //     } catch (\Exception $e) {
    //         \Log::error('Erreur lors de la restauration de la dette : ' . $e->getMessage());

    //         return response()->json([
    //             'success' => false,
    //             'message' => "Une erreur s'est produite lors de la restauration de la dette : " . $e->getMessage()
    //         ], 500);
    //     }


    // }


    // // Endpoint pour restaurer les dettes par client_id
    // public function restoreDettesByClientId($clientId)
    // {
    //     $user = Auth::user();
    //     if (!$user) {
    //         return response()->json([
    //             'message' => "Connectez vous d'abord."
    //         ], 403);
    //     }
    //     if (!in_array($user->role, ['admin', 'boutiquier'])) {
    //         return response()->json([
    //             'message' => 'Autorisation rejettée. Seuls les admins peuvent lister les utilisateurs.'
    //         ], 403);
    //     }
    //     $this->detteService->restoreDettesByClientId($clientId);

    //     return response()->json([
    //         'success' => true,
    //         'message' => "Les dettes du client $clientId ont été restaurées avec succès."
    //     ]);
    // }

}
