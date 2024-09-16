<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\Dette;
use App\Models\Notification;
use App\Services\InfoBipSmsService;
use App\Services\TwilioService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class NotifyOverdueDebtsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $today = Carbon::today();

        // Récupérer toutes les dettes non soldées dont la date d'échéance est passée
        $overdueDebts = Dette::whereRaw('COALESCE(montant, 0) > COALESCE(montant_paiement, 0)')
            ->whereDate('dateEcheance', '<', $today)
            ->get();

        // Grouper les dettes par client
        $debtsGroupedByClient = $overdueDebts->groupBy('client_id');

        // Parcourir chaque groupe de dettes par client
        foreach ($debtsGroupedByClient as $clientId => $clientDebts) {
            $client = Client::find($clientId);

            if ($client) {
                
                // Calculer le montant total des dettes en retard
                $totalOverdue = $clientDebts->sum(function ($dette) {
                    return $dette->montant - $dette->montant_paiement;
                });

                // Préparer le message
                $message = "Bonjour " . $client->nom_complet . ", vous avez un montant total de " . $totalOverdue . " Fr de dettes en retard. Merci de régulariser votre situation.";

                // Envoyer le SMS via un service
                $smsProvider = env('SMS_PROVIDER', 'infobip');
                $smsService = null;

                if ($smsProvider === 'twilio') {
                    $smsService = app(TwilioService::class);
                } elseif ($smsProvider === 'infobip') {
                    $smsService = app(InfoBipSmsService::class);
                }

                $smsService->sendSms($client->telephone, $message);

                // Enregistrer la notification dans la base de données
                Notification::create([
                    'client_id' => $client->id,
                    'content' => $message,
                    'is_read' => false,
                ]);
            }
        }
    }
}
