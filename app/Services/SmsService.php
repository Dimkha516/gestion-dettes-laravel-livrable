<?php

namespace App\Services;

use GuzzleHttp\Client;

class SmsService
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = 'https://api.infobip.com/sms/2/text/advanced';
        $this->apiKey = config('services.infobip.api_key'); // Assurez-vous de définir cette clé dans votre fichier de configuration
    }

    public function sendSms($to, $message)
    {
        $response = $this->client->post($this->baseUrl, [
            'headers' => [
                'Authorization' => 'App ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'messages' => [
                    [
                        'from' => 'InfoSMS',
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

        return $response->getBody()->getContents();
    }
}
