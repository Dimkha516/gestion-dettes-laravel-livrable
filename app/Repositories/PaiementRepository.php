<?php
namespace App\Repositories;

use App\Models\PaiementDette;
use App\Models\Dette;
class PaiementRepository
{
    public function createPayment($detteId, $amount)
    {
        return PaiementDette::create([
            'dette_id' => $detteId,
            'montant' => $amount,
        ]);
    }

    public function updateDettePayment($detteId, $paymentAmount)
    {
        $dette = Dette::find($detteId);
        if ($dette) {
            $dette->montant_paiement += $paymentAmount;
            $dette->save();
        }
    }

    public function getDette($detteId)
    {
        return Dette::find($detteId);
    }
}