<?php

namespace App\Jobs;
use App\Services\TwilioService;
use App\Services\InfoBipSmsService;  // Import du service InfoBip
use App\Models\Client;
use App\Models\Dette;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Notifications\RappelDetteSmsNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Queue\Jobs\Job;
use Illuminate\Queue\SerializesModels;

class RappelDetteSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */

    public function getUnSoldedDebts()
    {
        $unSolded = Dette::whereRaw('COALESCE(montant, 0) > COALESCE(montant_paiement, 0)')->get();
        return $unSolded;
    }
    public function handle()
    {
        $smsProvider = env('SMS_PROVIDER', 'infobip');  // Par défaut sur twilio
        $smsService = null;
    
        // Sélection du service de SMS en fonction du fournisseur
        if ($smsProvider === 'twilio') {
            $smsService = app(TwilioService::class);
        } elseif ($smsProvider === 'infobip') {
            $smsService = app(InfoBipSmsService::class);
        }
    
        // Récupération des dettes et envoi du SMS via le bon service
        $dettes = $this->getUnSoldedDebts();
        foreach ($dettes as $dette) {
            $client = Client::find($dette->client_id);
            $message = "Bonjour " . $client->surname . ", il vous reste " . ($dette->montant - $dette->montant_paiement) . "Fr à payer. Merci.";
            // $message = "bonjour";
            // Envoi du message via le service sélectionné
            $smsService->sendSms($client->telephone, $message);
        }
    }



    
    // public function handle(TwilioService $twilioService, InfoBipSmsService $infoBipSmsService)
    // {
    //     $dettes = $this->getUnSoldedDebts();
    //     $smsProvider = config('services.sms_provider', 'twilio'); // Choix du service dans .env (par défaut: Twilio)


    //     // // Envoyer un SMS à chaque client avec une dette non soldée
    //     foreach ($dettes as $dette) {
    //         $restant = $dette->montant - $dette->montant_paiement;
    //         $client = Client::find($dette->client_id);
    //         $message = "Bonjour cher(e) " . $client->surname .
    //             "Nous vous rapellons votre dette de " . $restant . "Fr. Veuillez régler dès que possible. Merci. 
    //         Ne répondez pas à ce message, c'est un test d'application";

    //         // Utilisation de Twilio ou InfoBip selon la configuration
    //         if ($smsProvider === 'twilio') {
    //             $twilioService->sendSms($client->telephone, $message);
    //         } elseif ($smsProvider === 'infobip') {
    //             $infoBipSmsService->sendSms($client->telephone, $message);
    //         }


    //     }
    // }

  

}
