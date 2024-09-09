<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaiementService;
use Illuminate\Http\JsonResponse;
class PaiementController extends Controller
{
    protected $paiementService;

    public function __construct(PaiementService $paiementService)
    {
        $this->paiementService = $paiementService;
    }

    public function index(): JsonResponse{
        $payments = $this->paiementService->getAllPayments();

        return response()->json([
            'success' => $payments['success'],
            'message' => $payments['message'],
            'data' => $payments['data'] ?? null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'dette_id' => 'required|exists:dettes,id',
            'montant' => 'required|numeric|min:0'
        ]);

        try {
            $this->paiementService->addPayment($request->dette_id, $request->montant);

            return response()->json([
                'success' => true,
                'message' => 'Paiement enregistré avec succès'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
