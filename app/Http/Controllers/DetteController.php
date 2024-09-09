<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\DetteRequest;
use App\Services\DetteService;
use Illuminate\Http\JsonResponse;

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
        $dettes = $this->detteService->getAllDettes();

        return response()->json([
            'success' => $dettes['success'],
            'message' => $dettes['message'],
            'data' => $dettes['data'] ?? null,
        ]);
    }

    public function store(DetteRequest $request)
    {
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


}
