<?php

namespace App\Http\Controllers;

use App\Jobs\RappelDetteSmsJob;
use App\Models\Dette;
use App\Services\InfoBipSmsService;
use App\Services\NotificationService;
use App\Services\TwilioService;
use Auth;
use DB;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Notification;
use App\Services\SmsService; // Assurez-vous que votre service SMS est importé correctement
use Illuminate\Foundation\Bus\Dispatchable;
use Log;


class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }


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
                'message' => 'Autorisation rejettée. Seuls les admins peuvent envoyer des notifications aux clients !.'
            ], 403);
        }

        try {
            $result = $this->notificationService->notifyClient($clientId);
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }

    }


    //------------- NOTIFICATION POUR PLUSIEURS CLIENTS:
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
                'message' => 'Autorisation rejettée. Seuls les admins peuvent envoyer des notifications aux clients !.'
            ], 403);
        }

        $this->notificationService->notifyAllClients();

        return response()->json(['message' => 'Notifications sent successfully'], 200);
    }


    //--------------------------ENVOIE DE NOTIFICATION PERSONNALISÉE AVEC SAISIE: 
    public function sendCustomNotification(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Connectez vous d'abord."
            ], 403);
        }
        if (!in_array($user->role, ['admin'])) {
            return response()->json([
                'message' => 'Autorisation rejettée. Seuls les admins peuvent envoyer des notifications aux clients !.'
            ], 403);
        }

        // Valider les entrées du message personnalisé
        $request->validate([
            'message' => 'required|string',
        ]);

        $messageTemplate = $request->input('message');

        $result = $this->notificationService->sendCustomNotification($messageTemplate);

        return response()->json($result, 200);
    }


    public function listNotifications(Request $request)
    {
        // Vérification de l'utilisateur connecté
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => "Connectez-vous d'abord."
            ], 403);
        }
    
    
        // Vérification que l'utilisateur est bien un client en cherchant dans la table Client
        $client = Client::where('user_id', $user->id)->first();
        if (!$client) {
        return response()->json([
        'message' => 'Autorisation rejetée. Seuls les clients peuvent consulter leurs notifications !'
        ], 403);
        }

        // Récupération du paramètre "lu"
        $lu = $request->query('lu');

        // Valider la valeur de "lu"
        if (!in_array($lu, ['oui', 'non'])) {
            return response()->json([
                'message' => 'Le paramètre "lu" doit être "oui" ou "non".'
            ], 400);
        }

        // Liste des notifications non lues (is_read = 0) ou lues (is_read = 1)
        $isRead = $lu === 'oui' ? 1 : 0;

        // Récupérer les notifications du client connecté
        $notifications = Notification::where('client_id', $client->id)
            ->where('is_read', $isRead)
            ->get();

        // Si aucune notification n'est trouvée
        if ($notifications->isEmpty()) {
            return response()->json([
                'message' => 'Aucune notification trouvée.'
            ], 200);
        }

        // Si on affiche les notifications non lues, on marque comme "lues"
        if ($lu === 'non') {
            Notification::where('client_id', $client->id)
                ->where('is_read', 0)
                ->update(['is_read' => 1]);
        }

        // Retourner les notifications
        return response()->json([
            'notifications' => $notifications
        ], 200);
    }




}
