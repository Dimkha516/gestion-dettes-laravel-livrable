<?php
namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Infobip\Api\SmsApi;
use Infobip\Configuration;
use App\Services\SmsServiceInterface;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;



class InfoBipSmsService implements SmsServiceInterface
{

    protected $smsApi;
    protected $client;
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        // // Initialisation de l'API InfoBip avec la clé et l'URL de base
        // $this->smsApi = new SmsApi(
        //     new Configuration(
        //         env('INFOBIP_API_KEY'),  // Clé API InfoBip
        //         env('INFOBIP_BASE_URL')  // URL de base InfoBip
        //     )
        // );

        $this->client = new Client();
        $this->baseUrl = 'https://api.infobip.com/sms/2/text/advanced';
        $this->apiKey = config('services.infobip.api_key');
    }

    public function sendSms(string $to, string $message)
    {
        try {
            // Création d'une destination avec le numéro du destinataire
            // $destination = new SmsDestination($to); // Directement une chaîne, non un tableau


            // Préparer les données de la requête
            $response = $this->client->post($this->baseUrl, [
                'headers' => [
                    'Authorization' => 'App ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'messages' => [
                        [
                            'from' => 'InfoSMS', // Assurez-vous que ce champ est défini correctement
                            'destinations' => [
                                [
                                    'to' => $to,
                                ],
                            ],
                            'text' => $message,
                        ],
                    ],
                ],
            ]);

            // return $response;
            return $response->getBody()->getContents();

        } catch (\Exception $e) {
            // Gestion des erreurs avec un log
            // \Log::error("Erreur InfoBip: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw new \Exception("Erreur lors de l'envoi du SMS via InfoBip : " . $e->getMessage());
        }
    }

    // protected $client;
    // protected $smsApi;
    // protected $baseUrl;
    // protected $apiKey;
    // public function __construct()
    // {

    //     $apiKey = env('INFOBIP_API_KEY');
    //     $baseUrl = env('INFOBIP_BASE_URL');



    //     // Initialisation de la configuration InfoBip
    //     // $configuration = new Configuration($baseUrl, $apiKey);
    //     $configuration = new Configuration($baseUrl, $apiKey);

    //     // Création d'une instance du service SMS
    //     $this->smsApi = new SmsApi($configuration);

    // }

    // //Fonction pour envoyer un SMS via InfoBip
    // public function sendSms($recipient, $message)
    // {
    //     try {
    //         // Création de la destination avec le numéro du destinataire
    //         $destination = new SmsDestination($recipient);  // Passer simplement le numéro de téléphone


    //         // Création du message avec le texte du SMS
    //         $smsMessage = new SmsTextualMessage([
    //             'from' => env('INFOBIP_FROM'), // Passer l'expéditeur ici dans le constructeur
    //             'destinations' => [$destination], // Ajouter la destination ici
    //             'text' => $message  // Le texte du message
    //         ]);

    //         // Création de la requête SMS
    //         $smsRequest = new SmsAdvancedTextualRequest([
    //             'messages' => [$smsMessage]  // Passer le message ici dans le constructeur
    //         ]);

    //         // Log des données envoyées
    //         \Log::info('SMS Request Data', [
    //             'recipient' => $recipient,
    //             'message' => $message,
    //             'request' => json_encode($smsRequest),
    //         ]);

    //         // Envoyer le SMS en utilisant l'API InfoBip
    //         $response = $this->smsApi->sendSmsMessage($smsRequest);
    //         return $response;
    //     }
    //     // 
    //     catch (\Exception $e) {
    //         throw new \Exception("Erreur lors de l'envoi du SMS via InfoBip : " . $e->getMessage());
    //     }
    // }
}


/*

try{
// Créer l'objet destination avec le numéro de téléphone du destinataire
            $destination = new SmsDestination($recipient);

            

            // Créer l'objet message avec le texte du SMS
            $smsMessage = new SmsTextualMessage([
                'from' => env('INFOBIP_FROM'),
                'destinations' => [$destination],
                'text' => $message,
            ]);

            // Créer la requête SMS avec le message
            $smsRequest = new SmsAdvancedTextualRequest([
                'messages' => [$smsMessage],
            ]);

            // Log des données envoyées
            \Log::info('SMS Request Data', [
                'recipient' => $recipient,
                'message' => $message,
                'request' => $smsRequest, // Log l'objet directement
            ]);

            // Envoyer le SMS en utilisant l'API InfoBip
            $response = $this->smsApi->sendSmsMessage($smsRequest);
            return $response;

}

*/