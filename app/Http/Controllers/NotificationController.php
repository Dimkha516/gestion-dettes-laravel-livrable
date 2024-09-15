<?php

namespace App\Http\Controllers;

use App\Models\Dette;
use App\Services\InfoBipSmsService;
use App\Services\TwilioService;
use Auth;
use DB;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Notification;
use App\Services\SmsService; // Assurez-vous que votre service SMS est importé correctement
use Illuminate\Foundation\Bus\Dispatchable;


class NotificationController extends Controller
{   

    protected $smsService;
    protected $smsProvider;
    public public function __construct() {
        
        $this->smsProvider = env('SMS_PROVIDER', 'infobip');  // Par défaut sur infobip
        $this->smsService = null;

        // Sélection du service de SMS en fonction du fournisseur
        if ($this->smsProvider === 'twilio') {
            $this->smsService = app(TwilioService::class);
        } elseif ($this->smsProvider === 'infobip') {
            $this->smsService = app(InfoBipSmsService::class);
        }   
    }

    public function notifyClient($clientId)
    {

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Connectez vous d'abord."
            ], 403);
        }
        if (!in_array($user->role, ['admin'])) {
            return response()->json([
                'message' => 'Autorisation rejettée. Seuls les admins  peuvent envoyer des notifications aux clients !.'
            ], 403);
        }


        // Récupérer le client  
        $client = Client::find($clientId);

        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        // Récupérer les dettes non soldées du client
        $totalDue = Dette::where('client_id', $clientId)
            ->whereRaw('COALESCE(montant, 0) > COALESCE(montant_paiement, 0)')
            ->sum(DB::raw('montant - montant_paiement'));

        if ($totalDue <= 0) {
            return response()->json(['message' => 'Ce client à soldé toutes ses dettes'], 200);
        }

        // Préparer le message
        $message = "Bonjour " . $client->surname . ", vous avez un montant total de " . $totalDue . " Fr à payer. Merci.";


        // Envoyer la notification par SMS
        $smsProvider = env('SMS_PROVIDER', 'infobip');  // Par défaut sur infobip
        // $smsService = null;
        $this->smsService = null;

        if ($smsProvider === 'twilio') {
            $this->smsService = app(TwilioService::class); 
            // $smsService = app(TwilioService::class);
        } elseif ($smsProvider === 'infobip') {
            $this->smsService = app(InfoBipSmsService::class);
            // $smsService = app(InfoBipSmsService::class);
        }

        // $smsService->sendSms($client->telephone, $message);
        $this->smsService->sendSms($client->telephone, $message);

        // Enregistrer la notification dans la base de données
        Notification::create([
            'client_id' => $client->id,
            'content' => $message,
            'is_read' => false,
        ]);

        return response()->json(['message' => 'Notification sent successfully'], 200);
    }


    public function notifyAllClients()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Connectez vous d'abord."
            ], 403);
        }
        if (!in_array($user->role, ['admin'])) {
            return response()->json([
                'message' => 'Autorisation rejettée. Seuls les admins  peuvent envoyer des notifications aux clients !.'
            ], 403);
        }


    }

}
