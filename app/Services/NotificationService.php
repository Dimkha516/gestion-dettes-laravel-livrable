<?php
namespace App\Services;
use App\Repositories\NotificationRepository;
use App\Services\TwilioService;
use App\Services\InfoBipSmsService;
use Log;


class NotificationService
{
    protected $notificationRepository;
    // protected $smsService;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
        // $this->smsService = $smsService;
    }


    // NOTIFICATION POUR UN SEUL CLIENT:
    public function notifyClient($clientId)
    {

        $smsProvider = env('SMS_PROVIDER', 'infobip');  // Par défaut sur infobip
        $smsService = null;

        // Sélection du service de SMS en fonction du fournisseur
        if ($smsProvider === 'twilio') {
            $smsService = app(TwilioService::class);
        } elseif ($smsProvider === 'infobip') {
            $smsService = app(InfoBipSmsService::class);
        }


        $client = $this->notificationRepository->findClientById($clientId);

        if (!$client) {
            throw new \Exception('Client not found');
        }

        $totalDue = $this->notificationRepository->getTotalDueForClient($clientId);

        if ($totalDue <= 0) {
            return ['message' => 'Auncune dette non soldée pour ce client !'];
        }

        $message = "Bonjour cher(e) client " . $client->surname . ", nous vous rapellons votre dette totale de " . $totalDue . " Fr à payer. Merci.";

        $smsService->sendSms($client->telephone, $message);

        $this->notificationRepository->createNotification($client->id, $message);

        return ['message' => 'Notification envoyée au client avec succès !'];
    }



    //------- NOTIFICATION À PLUSIEURS CLIENTS:
    public function notifyAllClients()
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
        $debtsGroupedByClient = $this->notificationRepository->getUnSoldedDebtsGroupedByClient();

        foreach ($debtsGroupedByClient as $debtGroup) {
            $client = $this->notificationRepository->findClientById($debtGroup->client_id);
            if ($client) {
                $message = "Bonjour " . $client->nom_complet . ", vous avez un montant total de " . $debtGroup->total_due . " Fr à payer. Merci.";

                // Envoi du message via le service sélectionné
                $smsService->sendSms($client->telephone, $message);

                // Enregistrement de la notification dans la base de données
                $this->notificationRepository->createNotification($client->id, $message);
            }
        }

        // Log pour indiquer que les notifications ont été envoyées
        Log::info('Notifications dettes non soldées envoyées avec succès via ! ' . $smsProvider);
    }


    public function sendCustomNotification($messageTemplate)
    {
        $smsProvider = env('SMS_PROVIDER', 'infobip');  // Par défaut sur infobip
        $smsService = null;

        // Sélection du service de SMS en fonction du fournisseur
        if ($smsProvider === 'twilio') {
            $smsService = app(TwilioService::class);
        } elseif ($smsProvider === 'infobip') {
            $smsService = app(InfoBipSmsService::class);
        }

        // Récupérer toutes les dettes non soldées
        $unpaidDebts = $this->notificationRepository->getUnpaidDebts();

        if ($unpaidDebts->isEmpty()) {
            return ['message' => 'Aucun client avec dette non soldée trouvé'];
        }

        // Grouper les dettes par client
        $debtsGroupedByClient = $unpaidDebts->groupBy('client_id');

        // Parcourir chaque groupe de dettes par client
        foreach ($debtsGroupedByClient as $clientId => $clientDebts) {
            $client = $this->notificationRepository->findClientById($clientId);

            if ($client) {
                // Calculer le montant total des dettes non soldées
                $totalUnpaid = $clientDebts->sum(function ($dette) {
                    return $dette->montant - $dette->montant_paiement;
                });

                // Personnaliser le message en remplaçant les variables
                $message = str_replace(['{surnom_client}', '{montant_total}'], [$client->surname, $totalUnpaid], $messageTemplate);

                // Envoyer le message via le service SMS
                $smsService->sendSms($client->telephone, $message);

                // Enregistrer la notification dans la base de données
                $this->notificationRepository->createNotification($client->id, $message);
            }
        }

        return ['message' => 'Notifications personnalisées envoyées avec succès !'];
    }


}