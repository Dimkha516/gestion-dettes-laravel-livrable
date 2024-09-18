<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDebtRequest;
use App\Jobs\SendDebtRequestNotification;
use App\Services\DebtRequestService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;



class DebtRequestController extends Controller
{
    protected $debtRequestService;


    public function __construct(DebtRequestService $debtRequestService)
    {
        $this->debtRequestService = $debtRequestService;
    }

    public function store(StoreDebtRequest $request)
    {
        try {
            // Créer la demande de dette avec les données validées
            $demande = $this->debtRequestService->createDebtRequest($request->validated());

            dd($demande);

            // Déclencher le Job pour notifier les boutiquiers
            SendDebtRequestNotification::dispatch($demande);

            return response()->json([
                'message' => 'Demande de dette envoyée avec succès.',
                'data' => $demande,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 400); // On renvoie une réponse avec une erreur 400
        }
    }

    //--------------------LISTER LES DEMANDES DE DETTES DU CLIENT CONNECTÉ:

    public function listDebts(Request $request): JsonResponse
    {
        // Récupérer le client connecté (via l'ID de l'utilisateur authentifié)
        $clientId = Auth::user()->client->id;

        // Vérifier si un filtre par état est fourni, sinon prendre "encours" par défaut
        $etat = $request->query('etat', 'encours');

        try {
            // Récupérer les dettes du client avec filtrage par état
            $dettes = $this->debtRequestService->getClientDebts($clientId, $etat);

            return response()->json([
                'success' => true,
                'data' => $dettes,
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function allListDebts(Request $request): JsonResponse
    {

        // Vérifier si un filtre par état est fourni, sinon prendre "encours" par défaut
        $etat = $request->query('etat', 'encours');

        try {
            // Récupérer les dettes du client avec filtrage par état
            $dettes = $this->debtRequestService->getAllClientDebts($etat);

            return response()->json([
                'success' => true,
                'data' => $dettes,
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
    


}
