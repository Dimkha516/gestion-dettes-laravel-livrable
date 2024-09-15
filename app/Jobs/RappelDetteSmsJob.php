<?php

namespace App\Jobs;
use App\Models\Notification;
use App\Services\TwilioService;
use App\Services\InfoBipSmsService;  // Import du service InfoBip
use App\Models\Client;
use App\Models\Dette;
use DB;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Notifications\RappelDetteSmsNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Queue\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Log;

class RappelDetteSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */

    public function getUnSoldedDebtsGroupedByClient()
    {
        // Récupérer toutes les dettes non soldées
        $unSoldedDebts = Dette::whereRaw('COALESCE(montant, 0) > COALESCE(montant_paiement, 0)')
            ->select('client_id', DB::raw('SUM(montant - montant_paiement) as total_due'))
            ->groupBy('client_id')
            ->get();
        return $unSoldedDebts;

        // $unSolded = Dette::whereRaw('COALESCE(montant, 0) > COALESCE(montant_paiement, 0)')->get();
        // return $unSolded;
    }
    public function handle()
    {

        $smsProvider = env('SMS_PROVIDER', 'infobip');  // Par défaut sur infobip
        $smsService = null;

        // Sélection du service de SMS en fonction du fournisseur
        if ($smsProvider === 'twilio') {
            $smsService = app(TwilioService::class);
        } elseif ($smsProvider === 'infobip') {
            $smsService = app(InfoBipSmsService::class);
        }

        // Récupération des dettes regroupées par client
        $debtsGroupedByClient = $this->getUnSoldedDebtsGroupedByClient();

        foreach ($debtsGroupedByClient as $debtGroup) {
            $client = Client::find($debtGroup->client_id);
            if ($client) {
                $message = "Bonjour " . $client->nom_complet . ", vous avez un montant total de " . $debtGroup->total_due . " Fr à payer. Merci.";

                // Envoi du message via le service sélectionné
                $smsService->sendSms($client->telephone, $message);
                // Enregistrement de la notification dans la base de données
                Notification::create([
                    'client_id' => $client->id,
                    'content' => $message,
                    'is_read' => false,
                ]);
            }
        }

        // Log pour indiquer que les notifications ont été envoyées
        Log::info('Notifications dettes non soldées envoyées avec succès via ! ' . $smsProvider);

    }



    // $smsProvider = env('SMS_PROVIDER', 'infobip');  // Par défaut sur twilio
    // $smsService = null;

    // // Sélection du service de SMS en fonction du fournisseur
    // if ($smsProvider === 'twilio') {
    //     $smsService = app(TwilioService::class);
    // } elseif ($smsProvider === 'infobip') {
    //     $smsService = app(InfoBipSmsService::class);
    // }

    // // Récupération des dettes et envoi du SMS via le bon service
    // $dettes = $this->getUnSoldedDebts();
    // foreach ($dettes as $dette) {
    //     $client = Client::find($dette->client_id);
    //     $message = "Bonjour " . $client->surname . ", il vous reste " . ($dette->montant - $dette->montant_paiement) . "Fr à payer. Merci.";
    //     // $message = "bonjour";
    //     // Envoi du message via le service sélectionné
    //     $smsService->sendSms($client->telephone, $message);
    // }
    // Log::info('Notifications dettes non soldées envoyées avec succès via ! ' . $smsProvider);

}
