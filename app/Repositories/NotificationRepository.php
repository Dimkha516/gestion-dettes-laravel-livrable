<?php
namespace App\Repositories;
use App\Models\Client;
use App\Models\Dette;
use App\Models\Notification;
use DB;

class NotificationRepository
{

    // RÉCUPÉRER UN CLIENT PAR SON ID:
    public function findClientById($clientId)
    {
        return Client::find($clientId);
    }


    public function getUnpaidDebts()
    {
        return Dette::whereRaw('COALESCE(montant, 0) > COALESCE(montant_paiement, 0)')
            ->get();
    }

    //----------------LE TOTAL NON SOLDÉE D'UN SEUL CLIENT
    public function getTotalDueForClient($clientId)
    {
        return Dette::where('client_id', $clientId)
            ->whereRaw('COALESCE(montant, 0) > COALESCE(montant_paiement, 0)')
            ->sum(DB::raw('montant - montant_paiement'));
    }



    //----------------LE TOTAL NON SOLDÉE DE TOUS LES CLIENTS:
    public function getUnSoldedDebtsGroupedByClient()
    {
        return Dette::select('client_id', DB::raw('SUM(montant - montant_paiement) as total_due'))
            ->whereRaw('COALESCE(montant, 0) > COALESCE(montant_paiement, 0)')
            ->groupBy('client_id')
            ->get();
    }


    // //----------------ENREGISTREMENT NOTIFICATION:
    public function createNotification($clientId, $content)
    {
        return Notification::create([
            'client_id' => $clientId,
            'content' => $content,
            'is_read' => false,
        ]);
    }
}


