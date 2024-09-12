<?php

namespace App\Jobs;
use App\Services\TwilioService;
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

    protected $client;

    // public function __construct(Client $client)
    // {
    //     $this->client = $client;

    // }

    /**
     * Execute the job.
     */

    public function getUnSoldedDebts()
    {
        $unSolded = Dette::whereRaw('COALESCE(montant, 0) > COALESCE(montant_paiement, 0)')->get();
        return $unSolded;
    }
    public function handle(TwilioService $twilioService)
    {
        $dettes = $this->getUnSoldedDebts();

        // // Envoyer un SMS à chaque client avec une dette non soldée
        foreach ($dettes as $dette) {
            $restant = $dette->montant - $dette->montant_paiement;
            $client = Client::find($dette->client_id);
            $twilioService->sendSms($client->telephone, "Bonjour cher(e) " . $client->surname .
                "Nous vous rapellons votre dette de " . $restant . "Fr. Veuillez régler dès que possible. Merci. 
            Ne répondez pas à ce message, c'est un test d'application");
            // $client->notify(new RappelDetteSmsNotification($client, $dette));
        }


        // // Récupérer les dettes non soldées
        // $dettes = Dette::whereColumn('montant', '>', 'montant_paiement')->get();
        // $dettes = Dette::whereRaw('COALESCE(montant, 0) > COALESCE(montant_paiement, 0)')->get();

        // $message = "Bonjour " . $this->client->nom . ", vous avez une dette non soldée de " . $this->client->montant . "Fr. Veuillez régler dès que possible.";

        // // Utiliser le service Twilio pour envoyer le SMS
        // $twilioService->sendSms($this->client->telephone, $message);



        // // Récupérer les dettes non soldées
        // $dettes = Dette::whereColumn('montant', '>', 'montant_paiement')->get();
        // $dettes = Dette::whereRaw('COALESCE(montant, 0) > COALESCE(montant_paiement, 0)')->get();

        // // Envoyer un SMS à chaque client avec une dette non soldée
        // foreach ($dettes as $dette) {
        //     $client = Client::find($dette->client_id);

        //     // $client->notify(new RappelDetteSmsNotification($client, $dette));
        // }
    }
}
